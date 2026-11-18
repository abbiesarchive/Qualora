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
        $open = $s->is_active !== false;

        return $this->makeCheck(
            rule: 'is_active',
            label: __('Open for applications'),
            requirement: __('Currently open'),
            value: null,
            passed: $open,
            detail: $open ? __('This scholarship is currently listed as open.') : __('This scholarship is no longer listed as open.'),
        );
    }

    private function deadlineCheck(Scholarship $s): ?array
    {
        if ($s->deadline === null) {
            return null;
        }

        $open = ! $s->deadline->copy()->startOfDay()->lt(Carbon::today());
        $date = $s->deadline->translatedFormat('j M Y');

        return $this->makeCheck(
            rule: 'deadline',
            label: __('Deadline'),
            requirement: __('Apply by :date', ['date' => $date]),
            value: null,
            passed: $open,
            detail: $open ? __('The deadline is :date.', ['date' => $date]) : __('The deadline passed on :date.', ['date' => $date]),
        );
    }

    private function gpaCheck(?Profile $p, Scholarship $s): ?array
    {
        $min = $this->requirement($s, 'min_gpa');
        if ($min === null) {
            return null;
        }
        $min = (float) $min;
        $minText = number_format($min, 2);
        $label = __('GPA');
        $requirement = __('Minimum :value', ['value' => $minText]);

        if ($p?->gpa === null) {
            return $this->unknown('min_gpa', $label, $requirement, __('Add your GPA to check this rule.'));
        }

        $gpa = (float) $p->gpa;
        $value = number_format($gpa, 2);

        if ($gpa >= $min) {
            return $this->makeCheck('min_gpa', $label, $requirement, $value, true, __('GPA :value meets the minimum of :min.', ['value' => $value, 'min' => $minText]));
        }

        $gap = round($min - $gpa, 2);
        $near = $gap <= self::GPA_NEAR_MISS + 1e-9;
        $detail = __('GPA :value, required :min.', ['value' => $value, 'min' => $minText]);
        if ($near) {
            $detail .= ' '.__('Only :gap away. A slightly higher GPA next term could change this.', ['gap' => number_format($gap, 2)]);
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
            $min !== null && $max !== null && (int) $min === (int) $max => __('Semester :n', ['n' => $min]),
            $min !== null && $max !== null => __('Semester :min to :max', ['min' => $min, 'max' => $max]),
            $min !== null => __('Semester :min or later', ['min' => $min]),
            default => __('Up to semester :max', ['max' => $max]),
        };

        if ($p?->semester === null) {
            return $this->unknown('semester', __('Semester'), $requirement, __('Add your semester to check this rule.'));
        }

        $sem = (int) $p->semester;
        $ok = ($min === null || $sem >= (int) $min) && ($max === null || $sem <= (int) $max);

        return $this->makeCheck('semester', __('Semester'), $requirement, __('Semester :n', ['n' => $sem]), $ok, $ok
            ? __('You are in semester :n, within the range.', ['n' => $sem])
            : __('You are in semester :n, the range is: :range.', ['n' => $sem, 'range' => $requirement]));
    }

    private function ageCheck(?Profile $p, Scholarship $s): ?array
    {
        $min = $this->requirement($s, 'min_age');
        $max = $this->requirement($s, 'max_age');
        if ($min === null && $max === null) {
            return null;
        }

        $requirement = match (true) {
            $min !== null && $max !== null => __('Age :min to :max', ['min' => $min, 'max' => $max]),
            $min !== null => __('At least :min years old', ['min' => $min]),
            default => __('At most :max years old', ['max' => $max]),
        };

        if ($p?->age === null) {
            return $this->unknown('age', __('Age'), $requirement, __('Add your age to check this rule.'));
        }

        $age = (int) $p->age;
        $ok = ($min === null || $age >= (int) $min) && ($max === null || $age <= (int) $max);

        return $this->makeCheck('age', __('Age'), $requirement, __(':n years', ['n' => $age]), $ok, $ok
            ? __('Age :n is within the limit.', ['n' => $age])
            : __('Age :n, the limit is: :limit.', ['n' => $age, 'limit' => $requirement]));
    }

    private function financialNeedCheck(?Profile $p, Scholarship $s): ?array
    {
        if ($this->requirement($s, 'requires_financial_need') !== true) {
            return null;
        }
        $label = __('Financial need');
        $requirement = __('For students with financial need');

        if ($p?->has_financial_need === null) {
            return $this->unknown('financial_need', $label, $requirement, __('Add your financial situation to check this rule.'));
        }

        $ok = $p->has_financial_need === true;

        return $this->makeCheck('financial_need', $label, $requirement, $ok ? __('Yes') : __('No'), $ok, $ok
            ? __('You indicated financial need, which this scholarship looks for.')
            : __('This scholarship is for students with financial need.'));
    }

    private function majorCheck(?Profile $p, Scholarship $s): ?array
    {
        $majors = $this->list($this->requirement($s, 'majors'));
        if ($majors === []) {
            return null;
        }
        $requirement = implode(', ', $majors);

        if ($this->blank($p?->major)) {
            return $this->unknown('major', __('Major'), $requirement, __('Add your major to check this rule.'));
        }

        $ok = $this->inList($p->major, $majors);

        return $this->makeCheck('major', __('Major'), $requirement, $p->major, $ok, $ok
            ? __(':major is one of the accepted majors.', ['major' => $p->major])
            : __(':major, accepted majors: :list.', ['major' => $p->major, 'list' => $requirement]));
    }

    private function campusCheck(?Profile $p, Scholarship $s): ?array
    {
        if ($this->blank($s->campus)) {
            return null; // national scholarship
        }

        if ($this->blank($p?->campus)) {
            return $this->unknown('campus', __('Campus'), $s->campus, __('Add your campus to check this rule.'));
        }

        $ok = mb_strtolower(trim($p->campus)) === mb_strtolower(trim($s->campus));

        return $this->makeCheck('campus', __('Campus'), $s->campus, $p->campus, $ok, $ok
            ? __('Your campus matches.')
            : __(':campus, this scholarship is for :required.', ['campus' => $p->campus, 'required' => $s->campus]));
    }

    private function nationalityCheck(?Profile $p, Scholarship $s): ?array
    {
        $allowed = $this->list($this->requirement($s, 'allowed_nationalities'));
        if ($allowed === []) {
            return null;
        }
        $requirement = implode(', ', $allowed);

        if ($this->blank($p?->nationality)) {
            return $this->unknown('nationality', __('Nationality'), $requirement, __('Add your nationality to check this rule.'));
        }

        $ok = $this->inList($p->nationality, $allowed);

        return $this->makeCheck('nationality', __('Nationality'), $requirement, $p->nationality, $ok, $ok
            ? __(':nationality is accepted.', ['nationality' => $p->nationality])
            : __(':nationality, accepted: :list.', ['nationality' => $p->nationality, 'list' => $requirement]));
    }

    private function englishCheck(?Profile $p, Scholarship $s): ?array
    {
        $type = $s->english_test_type;
        $min = $s->min_english_score;
        if ($this->blank($type) || $min === null) {
            return null;
        }
        $min = (float) $min;
        $minText = rtrim(rtrim(number_format($min, 1), '0'), '.');
        $requirement = __(':type :score or higher', ['type' => $type, 'score' => $minText]);

        // Only a score from the same test can be compared. Anything else is unknown.
        $match = $p?->languages
            ->first(fn ($lang) => ! $this->blank($lang->certificate_type)
                && mb_strtolower(trim($lang->certificate_type)) === mb_strtolower(trim($type))
                && is_numeric($lang->certificate_score));

        if ($match === null) {
            return $this->unknown('english', __('English test'), $requirement, __('Add your :type score to check this rule.', ['type' => $type]));
        }

        $score = (float) $match->certificate_score;
        $ok = $score >= $min;
        $yours = "{$type} {$match->certificate_score}";

        return $this->makeCheck('english', __('English test'), $requirement, $yours, $ok, $ok
            ? __(':yours meets the minimum.', ['yours' => $yours])
            : __(':yours, required :min.', ['yours' => $yours, 'min' => $minText]));
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
