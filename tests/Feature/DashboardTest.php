<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_shows_stats_and_empty_tracker(): void
    {
        $user = User::factory()->create(['name' => 'Bee Tan']);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Bee.')
            ->assertSee('Tracked')
            ->assertSee('Find scholarships')
            ->assertSee('Belum ada lamaran yang dipantau')
            ->assertDontSee('Closing soon');
    }

    public function test_dashboard_lists_tracked_applications(): void
    {
        $user = User::factory()->create();
        $scholarship = Scholarship::forceCreate([
            'name' => 'Tracked One', 'provider' => 'Test Foundation', 'level' => 'S1',
            'official_url' => 'https://example.org', 'last_verified' => today(),
            'year' => 2026, 'is_active' => true,
        ]);
        $user->applications()->create([
            'scholarship_id' => $scholarship->id, 'status' => 'accepted', 'applied_at' => today(),
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Tracked One');
    }
}
