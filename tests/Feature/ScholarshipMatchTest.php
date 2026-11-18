<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipMatchTest extends TestCase
{
    use RefreshDatabase;

    private function scholarship(array $attrs = []): Scholarship
    {
        return Scholarship::forceCreate($attrs + [
            'name' => 'Match Test', 'provider' => 'Test Foundation', 'level' => 'S1',
            'official_url' => 'https://example.org', 'last_verified' => today(),
            'year' => 2026, 'is_active' => true, 'min_gpa' => 3.25,
        ]);
    }

    public function test_detail_page_shows_the_exact_gap_for_a_near_miss(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['gpa' => 3.20]);

        $this->actingAs($user)->get(route('scholarships.show', $this->scholarship()))
            ->assertOk()
            ->assertSee('Almost there')
            ->assertSee('GPA 3.20, required 3.25')
            ->assertSee('0 of 1 requirements met')
            ->assertSee('not a guarantee');
    }

    public function test_detail_page_asks_for_missing_data_instead_of_failing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('scholarships.show', $this->scholarship()))
            ->assertOk()
            ->assertSee('You may be eligible')
            ->assertSee('Add your GPA to check this rule.');
    }
}
