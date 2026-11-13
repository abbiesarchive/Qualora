<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use App\Models\UserApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_applications_tracker_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Pemantauan Lamaran');
    }

    public function test_user_can_track_a_scholarship_application(): void
    {
        $user = User::factory()->create();
        $scholarship = Scholarship::forceCreate([
            'name' => 'Test Tracker Scholarship',
            'provider' => 'Test Provider',
            'level' => 'S1',
            'official_url' => 'https://example.org',
            'year' => 2026,
            'is_active' => true,
            'last_verified' => today(),
        ]);

        $response = $this->actingAs($user)->post(route('applications.store', $scholarship), [
            'status' => 'submitted',
            'applied_at' => today()->format('Y-m-d'),
            'registration_number' => 'REG-12345',
            'notes' => 'Applied on portal successfully',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_applications', [
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'submitted',
            'registration_number' => 'REG-12345',
        ]);
    }

    public function test_user_can_update_tracked_application_status(): void
    {
        $user = User::factory()->create();
        $scholarship = Scholarship::forceCreate([
            'name' => 'Status Test Scholarship',
            'provider' => 'Test Provider',
            'level' => 'S1',
            'official_url' => 'https://example.org',
            'year' => 2026,
            'is_active' => true,
            'last_verified' => today(),
        ]);

        $application = UserApplication::create([
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'submitted',
            'applied_at' => today(),
        ]);

        $this->actingAs($user)->patch(route('applications.update', $application), [
            'status' => 'interview',
            'registration_number' => 'REG-12345',
            'notes' => 'Interview scheduled next Monday',
        ])->assertRedirect();

        $this->assertDatabaseHas('user_applications', [
            'id' => $application->id,
            'status' => 'interview',
            'notes' => 'Interview scheduled next Monday',
        ]);
    }

    public function test_user_can_delete_tracked_application(): void
    {
        $user = User::factory()->create();
        $scholarship = Scholarship::forceCreate([
            'name' => 'Delete Test Scholarship',
            'provider' => 'Test Provider',
            'level' => 'S1',
            'official_url' => 'https://example.org',
            'year' => 2026,
            'is_active' => true,
            'last_verified' => today(),
        ]);

        $application = UserApplication::create([
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'submitted',
            'applied_at' => today(),
        ]);

        $this->actingAs($user)->delete(route('applications.destroy', $application))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_applications', [
            'id' => $application->id,
        ]);
    }
}
