<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use App\Models\UserApplication;
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
            ->assertSee('0%')
            ->assertSee('filled in')
            ->assertSee('Add my skills')
            ->assertDontSee('Saved scholarships');
    }

    public function test_dashboard_does_not_show_closing_soon_scholarships(): void
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
            ->assertDontSee('Closing soon')
            ->assertDontSee('Open One')
            ->assertDontSee('Past One');
    }

    public function test_dashboard_shows_total_accepted_and_rejected_application_counts(): void
    {
        $user = User::factory()->create();
        $base = [
            'provider' => 'Test Foundation',
            'level' => 'S1',
            'official_url' => 'https://example.org/apply',
            'year' => 2026,
            'is_active' => true,
            'last_verified' => today(),
        ];

        foreach (['submitted', 'accepted', 'rejected', 'rejected'] as $index => $status) {
            $scholarship = Scholarship::forceCreate($base + ['name' => "Application {$index}"]);
            UserApplication::create([
                'user_id' => $user->id,
                'scholarship_id' => $scholarship->id,
                'status' => $status,
                'applied_at' => today(),
            ]);
        }

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Total applications')
            ->assertSee('Accepted')
            ->assertSee('Not selected')
            ->assertViewHas('applicationCounts', [
                'total' => 4,
                'accepted' => 1,
                'rejected' => 2,
            ]);
    }
}
