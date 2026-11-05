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

    public function test_user_without_profile_sees_empty_states(): void
    {
        $user = User::factory()->create(['name' => 'Bee Tan']);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Bee.')
            ->assertSee('0% filled in')
            ->assertSee('Add my details')
            ->assertSee('Nothing saved yet');
    }

    public function test_closing_soon_lists_open_scholarships_with_official_link(): void
    {
        $user = User::factory()->create();
        $row = [
            'provider' => 'Test Foundation', 'level' => 'S1', 'scholarship_type' => 'full',
            'is_abroad' => false, 'official_url' => 'https://example.org/apply',
            'last_verified' => today(), 'year' => 2026, 'is_active' => true,
        ];
        Scholarship::forceCreate($row + ['name' => 'Open One', 'deadline' => today()->addDays(5)]);
        Scholarship::forceCreate($row + ['name' => 'Past One', 'deadline' => today()->subDay()]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Open One')
            ->assertSee('https://example.org/apply', false)
            ->assertDontSee('Past One');
    }
}
