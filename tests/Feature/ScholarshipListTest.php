<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('scholarships.index'))->assertRedirect('/login');
    }

    public function test_user_can_search_and_filter_scholarships(): void
    {
        $user = User::factory()->create();
        $active = $this->createScholarship([
            'name' => 'Tech Merit Award',
            'provider' => 'North Foundation',
            'scholarship_type' => 'full',
            'is_active' => true,
            'open_date' => today()->subDay(),
            'deadline' => today(),
        ]);
        $this->actingAs($user)
            ->get(route('scholarships.index', ['search' => 'Tech', 'type' => 'full']))
            ->assertOk()
            ->assertSee($active->name)
            ->assertSee($active->provider)
            ->assertSee('All scholarships')
            ->assertSee('Saved')
            ->assertSee('My applications')
            ->assertSee('Minimum GPA')
            ->assertSee('Open date')
            ->assertSee('View detail')
            ->assertDontSee('Inactive');
    }

    public function test_user_only_sees_scholarships_that_are_active_and_open(): void
    {
        $user = User::factory()->create();
        $this->createScholarship([
            'name' => 'Currently Open',
            'open_date' => today(),
            'deadline' => today(),
        ]);
        $this->createScholarship([
            'name' => 'Inactive Grant',
            'is_active' => false,
        ]);
        $this->createScholarship([
            'name' => 'Closed Grant',
            'deadline' => today()->subDay(),
        ]);
        $this->createScholarship([
            'name' => 'Not Yet Open Grant',
            'open_date' => today()->addDay(),
        ]);

        $this->actingAs($user)
            ->get(route('scholarships.index'))
            ->assertOk()
            ->assertSee('Currently Open')
            ->assertDontSee('Inactive Grant')
            ->assertDontSee('Closed Grant')
            ->assertDontSee('Not Yet Open Grant');
    }

    public function test_user_can_view_scholarship_detail(): void
    {
        $user = User::factory()->create();
        $scholarship = $this->createScholarship(['name' => 'Merit Award']);

        $this->actingAs($user)
            ->get(route('scholarships.show', $scholarship))
            ->assertOk()
            ->assertSee('Merit Award')
            ->assertSee('Visit official website')
            ->assertSee('https://example.org/apply', false);
    }

    private function createScholarship(array $attributes = []): Scholarship
    {
        return Scholarship::forceCreate($attributes + [
            'name' => 'Test Scholarship '.uniqid(),
            'provider' => 'Test Foundation',
            'level' => 'S1',
            'scholarship_type' => 'full',
            'official_url' => 'https://example.org/apply',
            'last_verified' => today(),
            'year' => 2026,
            'is_active' => true,
        ]);
    }
}
