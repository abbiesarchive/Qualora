<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Scholarship;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Deterministic eligibility engine. Pure PHP: no AI, no HTTP, no writes.
 *
 * A check's `passed` is true (met), false (not met) or null (unknown because the
 * profile has no value to compare). Unknown never counts as failed.
 *
 * Context and preference fields (achievements, goals, study plan, ...) never pass
 * or fail a rule. The study plan may only nudge ordering inside a status group.
 */
class EligibilityService
{
    /** A GPA shortfall up to this much counts as a near miss. */
    public const GPA_NEAR_MISS = 0.15;

    /** Rules about the scholarship itself, not the student. */
    private const SCHOLARSHIP_RULES = ['is_active', 'deadline'];

    public const ELIGIBLE = 'eligible';

    public const ALMOST = 'almost';

    public const NOT_ELIGIBLE = 'not_eligible';

    /**
     * @return array{
     *     status: string,
     *     checks: list<array{rule: string, label: string, requirement: string, profile_value: ?string, passed: ?bool, detail: string, near_miss: bool}>,
     *     met: int,
     *     total: int,
     * }
     */
    public function check(?Profile $profile, Scholarship $scholarship): array
    {
        $checks = array_values(array_filter([
            $this->activeCheck($scholarship),
            $this->deadlineCheck($scholarship),
            $this->gpaCheck($profile, $scholarship),
            $this->semesterCheck($profile, $scholarship),
            $this->ageCheck($profile, $scholarship),
            $this->financialNeedCheck($profile, $scholarship),
            $this->majorCheck($profile, $scholarship),
            $this->campusCheck($profile, $scholarship),
            $this->nationalityCheck($profile, $scholarship),
            $this->englishCheck($profile, $scholarship),
        ]));

        $failed = array_values(array_filter($checks, fn (array $c) => $c['passed'] === false));

        $status = match (true) {
            $failed === [] => self::ELIGIBLE,
            count($failed) === 1 && $failed[0]['near_miss'] => self::ALMOST,
            default => self::NOT_ELIGIBLE,
        };

        // "X of Y requirements met" counts rules about the student only, not whether
        // the scholarship is open.
        $about = array_filter($checks, fn (array $c) => ! in_array($c['rule'], self::SCHOLARSHIP_RULES, true));

        return [
            'status' => $status,
            'checks' => $checks,
            'met' => count(array_filter($about, fn (array $c) => $c['passed'] === true)),
            'total' => count($about),
        ];
    }

    /**
     * Check every scholarship and order the results: eligible, then almost, then
     * not eligible. Inside a group, scholarships matching the student's preferred
     * study location come first, then the nearest deadline.
     *
     * @param  iterable<Scholarship>  $scholarships
     * @return Collection<int, array{scholarship: Scholarship, result: array}>
     */
    public function rank(?Profile $profile, iterable $scholarships): Collection
    {
        $order = [self::ELIGIBLE => 0, self::ALMOST => 1, self::NOT_ELIGIBLE => 2];

        return collect($scholarships)
            ->map(fn (Scholarship $s) => ['scholarship' => $s, 'result' => $this->check($profile, $s)])
            ->sortBy([
                fn (array $a, array $b) => $order[$a['result']['status']] <=> $order[$b['result']['status']],
                fn (array $a, array $b) => $this->prefersLocation($profile, $b['scholarship']) <=> $this->prefersLocation($profile, $a['scholarship']),
                fn (array $a, array $b) => ($a['scholarship']->deadline?->timestamp ?? PHP_INT_MAX) <=> ($b['scholarship']->deadline?->timestamp ?? PHP_INT_MAX),
            ])
            ->values();
    }

    private function activeCheck(Scholarship $s): array
    {
        if ($s->is_active !== false) {
            return $this->makeCheck(rule: 'is_active', label: 'Open for applications', requirement: 'Currently open', value: null, passed: true, detail: 'This scholarship is currently listed as open.');
        }

        return $this->makeCheck(rule: 'is_active', label: 'Open for applications', requirement: 'Currently open', value: null, passed: false, detail: 'This scholarship is no longer listed as open.');
    }

    private function deadlineCheck(Scholarship $s): ?array
    {
        if ($s->deadline === null) {
            return null;
        }

        $open = ! $s->deadline->copy()->startOfDay()->lt(Carbon::today());
        $date = $s->deadline->format('j M Y');

        return $this->makeCheck(
            rule: 'deadline',
            label: 'Deadline',
            requirement: "Apply by {$date}",
            value: null,
            passed: $open,
            detail: $open ? "The deadline is {$date}." : "The deadline passed on {$date}.",
        );
    }

    private function gpaCheck(?Profile $p, Scholarship $s): ?array
    {
        $min = $this->requirement($s, 'min_gpa');
        if ($min === null) {
            return null;
        }
        $min = (float) $min;
        $label = 'GPA';
        $requirement = 'Minimum '.number_format($min, 2);

        if ($p?->gpa === null) {
            return $this->unknown('min_gpa', $label, $requirement, 'Add your GPA to check this rule.');
        }

        $gpa = (float) $p->gpa;
        $value = number_format($gpa, 2);

        if ($gpa >= $min) {
            return $this->makeCheck('min_gpa', $label, $requirement, $value, true, "GPA {$value} meets the minimum of ".number_format($min, 2).'.');
        }

        $gap = round($min - $gpa, 2);
        $near = $gap <= self::GPA_NEAR_MISS + 1e-9;
        $detail = "GPA {$value}, required ".number_format($min, 2).'.';
        if ($near) {
            $detail .= ' Only '.number_format($gap, 2).' away. A slightly higher GPA next term could change this.';
        }

        return $this->makeCheck('min_gpa', $label, $requirement, $value, false, $detail, $near);
    }

    private function semesterCheck(?Profile $p, Scholarship $s): ?array
    {
        $min = $this->requirement($s, 'min_semester');
        $max = $this->requirement($s, 'max_semester');
        if ($min === null && $max === null) {
            return null;
        }

        $requirement = match (true) {
            $min !== null && $max !== null && (int) $min === (int) $max => "Semester {$min}",
            $min !== null && $max !== null => "Semester {$min} to {$max}",
            $min !== null => "Semester {$min} or later",
            default => "Up to semester {$max}",
        };

        if ($p?->semester === null) {
            return $this->unknown('semester', 'Semester', $requirement, 'Add your semester to check this rule.');
        }

        $sem = (int) $p->semester;
        $ok = ($min === null || $sem >= (int) $min) && ($max === null || $sem <= (int) $max);

        return $this->makeCheck('semester', 'Semester', $requirement, "Semester {$sem}", $ok, $ok
            ? "You are in semester {$sem}, within the range."
            : "You are in semester {$sem}, the range is: {$requirement}.");
    }

    private function ageCheck(?Profile $p, Scholarship $s): ?array
    {
        $min = $this->requirement($s, 'min_age');
        $max = $this->requirement($s, 'max_age');
        if ($min === null && $max === null) {
            return null;
        }

        $requirement = match (true) {
            $min !== null && $max !== null => "Age {$min} to {$max}",
            $min !== null => "At least {$min} years old",
            default => "At most {$max} years old",
        };

        if ($p?->age === null) {
            return $this->unknown('age', 'Age', $requirement, 'Add your age to check this rule.');
        }

        $age = (int) $p->age;
        $ok = ($min === null || $age >= (int) $min) && ($max === null || $age <= (int) $max);

        return $this->makeCheck('age', 'Age', $requirement, "{$age} years", $ok, $ok
            ? "Age {$age} is within the limit."
            : "Age {$age}, the limit is: {$requirement}.");
    }

    private function financialNeedCheck(?Profile $p, Scholarship $s): ?array
    {
        if ($this->requirement($s, 'requires_financial_need') !== true) {
            return null;
        }
        $requirement = 'For students with financial need';

        if ($p?->has_financial_need === null) {
            return $this->unknown('financial_need', 'Financial need', $requirement, 'Add your financial situation to check this rule.');
        }

        $ok = $p->has_financial_need === true;

        return $this->makeCheck('financial_need', 'Financial need', $requirement, $ok ? 'Yes' : 'No', $ok, $ok
            ? 'You indicated financial need, which this scholarship looks for.'
            : 'This scholarship is for students with financial need.');
    }

    private function majorCheck(?Profile $p, Scholarship $s): ?array
    {
        $majors = $this->list($this->requirement($s, 'majors'));
        if ($majors === []) {
            return null;
        }
        $requirement = implode(', ', $majors);

        if ($this->blank($p?->major)) {
            return $this->unknown('major', 'Major', $requirement, 'Add your major to check this rule.');
        }

        $ok = $this->inList($p->major, $majors);

        return $this->makeCheck('major', 'Major', $requirement, $p->major, $ok, $ok
            ? "{$p->major} is one of the accepted majors."
            : "{$p->major}, accepted majors: {$requirement}.");
    }

    private function campusCheck(?Profile $p, Scholarship $s): ?array
    {
        if ($this->blank($s->campus)) {
            return null; // national scholarship
        }

        if ($this->blank($p?->campus)) {
            return $this->unknown('campus', 'Campus', $s->campus, 'Add your campus to check this rule.');
        }

        $ok = mb_strtolower(trim($p->campus)) === mb_strtolower(trim($s->campus));

        return $this->makeCheck('campus', 'Campus', $s->campus, $p->campus, $ok, $ok
            ? 'Your campus matches.'
            : "{$p->campus}, this scholarship is for {$s->campus}.");
    }

    private function nationalityCheck(?Profile $p, Scholarship $s): ?array
    {
        $allowed = $this->list($this->requirement($s, 'allowed_nationalities'));
        if ($allowed === []) {
            return null;
        }
        $requirement = implode(', ', $allowed);

        if ($this->blank($p?->nationality)) {
            return $this->unknown('nationality', 'Nationality', $requirement, 'Add your nationality to check this rule.');
        }

        $ok = $this->inList($p->nationality, $allowed);

        return $this->makeCheck('nationality', 'Nationality', $requirement, $p->nationality, $ok, $ok
            ? "{$p->nationality} is accepted."
            : "{$p->nationality}, accepted: {$requirement}.");
    }

    private function englishCheck(?Profile $p, Scholarship $s): ?array
    {
        $type = $s->english_test_type;
        $min = $s->min_english_score;
        if ($this->blank($type) || $min === null) {
            return null;
        }
        $min = (float) $min;
        $requirement = "{$type} ".rtrim(rtrim(number_format($min, 1), '0'), '.').' or higher';

        // Only a score from the same test can be compared. Anything else is unknown.
        $match = $p?->languages
            ->first(fn ($lang) => ! $this->blank($lang->certificate_type)
                && mb_strtolower(trim($lang->certificate_type)) === mb_strtolower(trim($type))
                && is_numeric($lang->certificate_score));

        if ($match === null) {
            return $this->unknown('english', 'English test', $requirement, "Add your {$type} score to check this rule.");
        }

        $score = (float) $match->certificate_score;
        $ok = $score >= $min;

        return $this->makeCheck('english', 'English test', $requirement, "{$type} {$match->certificate_score}", $ok, $ok
            ? "{$type} {$match->certificate_score} meets the minimum."
            : "{$type} {$match->certificate_score}, required ".rtrim(rtrim(number_format($min, 1), '0'), '.').'.');
    }

    /** Scholarship column first (what admins edit), then the detailed eligibility record. */
    private function requirement(Scholarship $s, string $field): mixed
    {
        $value = $s->{$field};
        if ($value === null || $value === []) {
            $value = $s->eligibility?->{$field};
        }

        return $value === [] ? null : $value;
    }

    /** @return list<string> */
    private function list(mixed $value): array
    {
        return array_values(array_filter(array_map(
            fn ($v) => trim((string) $v),
            is_array($value) ? $value : [],
        ), fn (string $v) => $v !== ''));
    }

    /** @param  list<string>  $list */
    private function inList(string $value, array $list): bool
    {
        return in_array(mb_strtolower(trim($value)), array_map('mb_strtolower', $list), true);
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function prefersLocation(?Profile $p, Scholarship $s): int
    {
        $wants = $p?->studyPlan?->preferred_country;

        return match (true) {
            $wants === null, $wants === '', $wants === 'Open to anywhere' => 0,
            $wants === 'Indonesia' => $s->is_abroad ? 0 : 1,
            default => $s->is_abroad ? 1 : 0,
        };
    }

    private function unknown(string $rule, string $label, string $requirement, string $detail): array
    {
        return $this->makeCheck($rule, $label, $requirement, null, null, $detail);
    }

    private function makeCheck(string $rule, string $label, string $requirement, ?string $value, ?bool $passed, string $detail, bool $nearMiss = false): array
    {
        return [
            'rule' => $rule,
            'label' => $label,
            'requirement' => $requirement,
            'profile_value' => $value,
            'passed' => $passed,
            'detail' => $detail,
            'near_miss' => $nearMiss,
        ];
    }
}
