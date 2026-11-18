<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Models\Scholarship;
use App\Models\ScholarshipEligibility;
use App\Models\UserLanguage;
use App\Models\UserStudyPlan;
use App\Services\EligibilityService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pure logic tests: models are built in memory, nothing touches the database.
 */
class EligibilityServiceTest extends TestCase
{
    private EligibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-18 09:00:00');
        $this->service = new EligibilityService;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function scholarship(array $attrs = []): Scholarship
    {
        $s = new Scholarship($attrs + ['name' => 'Test', 'is_active' => true]);
        $s->setRelation('eligibility', null);

        return $s;
    }

    private function profile(array $attrs = [], array $languages = []): Profile
    {
        $p = new Profile($attrs);
        $p->setRelation('languages', collect(array_map(fn (array $l) => new UserLanguage($l), $languages)));
        $p->setRelation('studyPlan', null);

        return $p;
    }

    private function rule(array $result, string $rule): ?array
    {
        foreach ($result['checks'] as $check) {
            if ($check['rule'] === $rule) {
                return $check;
            }
        }

        return null;
    }

    public function test_student_who_meets_every_rule_is_eligible(): void
    {
        $s = $this->scholarship([
            'min_gpa' => 3.25, 'min_semester' => 3, 'max_semester' => 6, 'max_age' => 23,
            'requires_financial_need' => true, 'majors' => ['Computer Science'],
            'allowed_nationalities' => ['Indonesian'], 'campus' => 'UI',
            'english_test_type' => 'IELTS', 'min_english_score' => 6.5,
            'deadline' => '2026-12-01',
        ]);
        $p = $this->profile([
            'gpa' => 3.60, 'semester' => 4, 'age' => 20, 'has_financial_need' => true,
            'major' => 'computer science', 'nationality' => 'Indonesian', 'campus' => 'UI',
        ], [['language_name' => 'English', 'certificate_type' => 'IELTS', 'certificate_score' => '7.0']]);

        $result = $this->service->check($p, $s);

        $this->assertSame('eligible', $result['status']);
        $this->assertSame($result['total'], $result['met']);
        $this->assertSame(8, $result['total']); // is_active and deadline are not counted
    }

    public function test_gpa_below_minimum_beyond_near_miss_fails(): void
    {
        $result = $this->service->check(
            $this->profile(['gpa' => 2.80]),
            $this->scholarship(['min_gpa' => 3.25]),
        );

        $gpa = $this->rule($result, 'min_gpa');
        $this->assertFalse($gpa['passed']);
        $this->assertFalse($gpa['near_miss']);
        $this->assertSame('not_eligible', $result['status']);
    }

    public function test_gpa_equal_to_minimum_passes(): void
    {
        $result = $this->service->check(
            $this->profile(['gpa' => 3.25]),
            $this->scholarship(['min_gpa' => 3.25]),
        );

        $this->assertTrue($this->rule($result, 'min_gpa')['passed']);
        $this->assertSame('eligible', $result['status']);
    }

    public function test_gpa_near_miss_is_almost_and_shows_the_exact_gap(): void
    {
        $result = $this->service->check(
            $this->profile(['gpa' => 3.20]),
            $this->scholarship(['min_gpa' => 3.25]),
        );

        $gpa = $this->rule($result, 'min_gpa');
        $this->assertFalse($gpa['passed']);
        $this->assertTrue($gpa['near_miss']);
        $this->assertStringContainsString('GPA 3.20, required 3.25', $gpa['detail']);
        $this->assertSame('almost', $result['status']);
    }

    public function test_gpa_shortfall_exactly_at_the_limit_is_still_a_near_miss(): void
    {
        $result = $this->service->check(
            $this->profile(['gpa' => 3.10]),
            $this->scholarship(['min_gpa' => 3.25]),
        );

        $this->assertTrue($this->rule($result, 'min_gpa')['near_miss']);
    }

    public function test_gpa_shortfall_just_over_the_limit_is_not_a_near_miss(): void
    {
        $result = $this->service->check(
            $this->profile(['gpa' => 3.09]),
            $this->scholarship(['min_gpa' => 3.25]),
        );

        $this->assertFalse($this->rule($result, 'min_gpa')['near_miss']);
        $this->assertSame('not_eligible', $result['status']);
    }

    public function test_two_failed_rules_are_not_eligible_even_if_one_is_a_near_miss(): void
    {
        $result = $this->service->check(
            $this->profile(['gpa' => 3.20, 'age' => 30]),
            $this->scholarship(['min_gpa' => 3.25, 'max_age' => 23]),
        );

        $this->assertSame('not_eligible', $result['status']);
    }

    public function test_a_failed_non_gpa_rule_alone_is_not_a_near_miss(): void
    {
        $result = $this->service->check(
            $this->profile(['age' => 25]),
            $this->scholarship(['max_age' => 23]),
        );

        $this->assertFalse($this->rule($result, 'age')['passed']);
        $this->assertSame('not_eligible', $result['status']);
    }

    public function test_semester_range_includes_both_ends(): void
    {
        $s = $this->scholarship(['min_semester' => 3, 'max_semester' => 6]);

        $this->assertTrue($this->rule($this->service->check($this->profile(['semester' => 3]), $s), 'semester')['passed']);
        $this->assertTrue($this->rule($this->service->check($this->profile(['semester' => 6]), $s), 'semester')['passed']);
        $this->assertFalse($this->rule($this->service->check($this->profile(['semester' => 2]), $s), 'semester')['passed']);
        $this->assertFalse($this->rule($this->service->check($this->profile(['semester' => 7]), $s), 'semester')['passed']);
    }

    public function test_age_at_the_maximum_passes_and_one_over_fails(): void
    {
        $s = $this->scholarship(['max_age' => 23]);

        $this->assertTrue($this->rule($this->service->check($this->profile(['age' => 23]), $s), 'age')['passed']);
        $this->assertFalse($this->rule($this->service->check($this->profile(['age' => 24]), $s), 'age')['passed']);
    }

    public function test_financial_need_required_but_student_has_none_fails(): void
    {
        $s = $this->scholarship(['requires_financial_need' => true]);

        $this->assertFalse($this->rule($this->service->check($this->profile(['has_financial_need' => false]), $s), 'financial_need')['passed']);
        $this->assertTrue($this->rule($this->service->check($this->profile(['has_financial_need' => true]), $s), 'financial_need')['passed']);
    }

    public function test_major_nationality_and_campus_fail_when_they_do_not_match(): void
    {
        $s = $this->scholarship([
            'majors' => ['Computer Science'], 'allowed_nationalities' => ['Indonesian'], 'campus' => 'UI',
        ]);
        $result = $this->service->check(
            $this->profile(['major' => 'Law', 'nationality' => 'Malaysian', 'campus' => 'ITB']),
            $s,
        );

        $this->assertFalse($this->rule($result, 'major')['passed']);
        $this->assertFalse($this->rule($result, 'nationality')['passed']);
        $this->assertFalse($this->rule($result, 'campus')['passed']);
    }

    public function test_empty_majors_nationalities_and_no_campus_mean_any_and_are_skipped(): void
    {
        $result = $this->service->check(
            $this->profile(['major' => 'Law']),
            $this->scholarship(['majors' => [], 'allowed_nationalities' => [], 'campus' => null]),
        );

        $this->assertNull($this->rule($result, 'major'));
        $this->assertNull($this->rule($result, 'nationality'));
        $this->assertNull($this->rule($result, 'campus'));
    }

    public function test_rules_the_scholarship_does_not_state_are_skipped(): void
    {
        $result = $this->service->check($this->profile(['gpa' => 1.0, 'age' => 99]), $this->scholarship());

        $this->assertNull($this->rule($result, 'min_gpa'));
        $this->assertNull($this->rule($result, 'age'));
        $this->assertNull($this->rule($result, 'semester'));
        $this->assertSame('eligible', $result['status']);
    }

    public function test_missing_profile_values_are_unknown_not_failed(): void
    {
        $s = $this->scholarship([
            'min_gpa' => 3.25, 'min_semester' => 3, 'max_age' => 23, 'requires_financial_need' => true,
            'majors' => ['Law'], 'allowed_nationalities' => ['Indonesian'], 'campus' => 'UI',
            'english_test_type' => 'IELTS', 'min_english_score' => 6.5,
        ]);
        $result = $this->service->check($this->profile(), $s);

        foreach (['min_gpa', 'semester', 'age', 'financial_need', 'major', 'nationality', 'campus', 'english'] as $rule) {
            $this->assertNull($this->rule($result, $rule)['passed'], "{$rule} should be unknown");
        }
        $this->assertSame('eligible', $result['status']);
        $this->assertStringContainsString('Add your GPA', $this->rule($result, 'min_gpa')['detail']);
    }

    public function test_a_missing_profile_is_treated_as_all_unknown(): void
    {
        $result = $this->service->check(null, $this->scholarship(['min_gpa' => 3.0]));

        $this->assertNull($this->rule($result, 'min_gpa')['passed']);
        $this->assertSame('eligible', $result['status']);
    }

    public function test_english_score_is_compared_only_for_the_same_test(): void
    {
        $s = $this->scholarship(['english_test_type' => 'IELTS', 'min_english_score' => 6.5]);

        $same = $this->service->check($this->profile([], [['language_name' => 'English', 'certificate_type' => 'IELTS', 'certificate_score' => '6.0']]), $s);
        $other = $this->service->check($this->profile([], [['language_name' => 'English', 'certificate_type' => 'TOEFL iBT', 'certificate_score' => '50']]), $s);
        $none = $this->service->check($this->profile(), $s);

        $this->assertFalse($this->rule($same, 'english')['passed']);
        $this->assertNull($this->rule($other, 'english')['passed']);
        $this->assertNull($this->rule($none, 'english')['passed']);
    }

    public function test_english_score_equal_to_minimum_passes(): void
    {
        $s = $this->scholarship(['english_test_type' => 'IELTS', 'min_english_score' => 6.5]);
        $result = $this->service->check($this->profile([], [['language_name' => 'English', 'certificate_type' => 'ielts', 'certificate_score' => '6.5']]), $s);

        $this->assertTrue($this->rule($result, 'english')['passed']);
    }

    public function test_deadline_today_is_open_and_yesterday_is_closed(): void
    {
        $today = $this->service->check($this->profile(), $this->scholarship(['deadline' => '2026-11-18']));
        $past = $this->service->check($this->profile(), $this->scholarship(['deadline' => '2026-11-17']));

        $this->assertTrue($this->rule($today, 'deadline')['passed']);
        $this->assertFalse($this->rule($past, 'deadline')['passed']);
        $this->assertSame('not_eligible', $past['status']);
    }

    public function test_inactive_scholarship_is_not_eligible(): void
    {
        $result = $this->service->check($this->profile(), $this->scholarship(['is_active' => false]));

        $this->assertFalse($this->rule($result, 'is_active')['passed']);
        $this->assertSame('not_eligible', $result['status']);
    }

    public function test_context_fields_never_change_the_result(): void
    {
        $s = $this->scholarship(['min_gpa' => 3.5]);
        $plain = $this->profile(['gpa' => 3.0]);
        $rich = $this->profile(['gpa' => 3.0]);
        $rich->achievements = 'National champion';
        $rich->goals = 'Study abroad';

        $this->assertSame($this->service->check($plain, $s), $this->service->check($rich, $s));
    }

    public function test_requirements_fall_back_to_the_detailed_eligibility_record(): void
    {
        $s = new Scholarship(['name' => 'Test', 'is_active' => true]);
        $s->setRelation('eligibility', new ScholarshipEligibility(['min_gpa' => 3.5, 'min_age' => 18]));

        $result = $this->service->check($this->profile(['gpa' => 3.0, 'age' => 17]), $s);

        $this->assertFalse($this->rule($result, 'min_gpa')['passed']);
        $this->assertFalse($this->rule($result, 'age')['passed']);
    }

    public function test_rank_orders_by_status_then_location_preference_then_deadline(): void
    {
        $profile = $this->profile(['gpa' => 3.5]);
        $plan = new UserStudyPlan(['preferred_country' => 'Europe']);
        $profile->setRelation('studyPlan', $plan);

        $notEligible = $this->scholarship(['name' => 'C', 'min_gpa' => 3.9, 'deadline' => '2026-11-20']);
        $laterHome = $this->scholarship(['name' => 'B-home', 'min_gpa' => 3.0, 'is_abroad' => false, 'deadline' => '2026-11-25']);
        $abroad = $this->scholarship(['name' => 'A-abroad', 'min_gpa' => 3.0, 'is_abroad' => true, 'deadline' => '2026-12-30']);

        $ranked = $this->service->rank($profile, [$notEligible, $laterHome, $abroad]);

        $this->assertSame(['A-abroad', 'B-home', 'C'], $ranked->pluck('scholarship.name')->all());
    }
}
