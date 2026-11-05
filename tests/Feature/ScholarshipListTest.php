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
        ]);
        $this->createScholarship([
            'name' => 'Arts Support Grant',
            'provider' => 'South Foundation',
            'scholarship_type' => 'partial',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('scholarships.index', ['search' => 'Tech', 'type' => 'full', 'status' => 'active']))
            ->assertOk()
            ->assertSee($active->name)
            ->assertSee($active->provider)
            ->assertSee('Minimum GPA')
            ->assertSee('Open date')
            ->assertSee('View detail')
            ->assertDontSee('Arts Support Grant');
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
