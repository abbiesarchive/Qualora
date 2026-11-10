<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_create_and_update_student_profile_information(): void
    {
        $user = User::factory()->create();
        $profileData = [
            'campus' => 'University of Indonesia',
            'major' => 'Computer Science',
            'semester' => 5,
            'gpa' => 3.75,
            'age' => 20,
            'nationality' => 'Indonesian',
            'has_financial_need' => '1',
            'english_test_type' => 'IELTS',
            'english_score' => 7.5,
            'achievements' => "Dean's List",
            'organizations' => 'Student Association',
            'goals' => "Pursue a master's degree",
            'preferred_scholarship_type' => 'full',
            'open_to_abroad' => '1',
            'circumstances' => 'First-generation student',
        ];

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                ...$profileData,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'campus' => 'University of Indonesia',
            'major' => 'Computer Science',
            'semester' => 5,
            'gpa' => '3.75',
            'has_financial_need' => 1,
            'english_test_type' => 'IELTS',
            'english_score' => '7.5',
            'preferred_scholarship_type' => 'full',
            'open_to_abroad' => 1,
            'circumstances' => 'First-generation student',
        ]);

        $this->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            ...$profileData,
            'campus' => 'Bandung Institute of Technology',
            'has_financial_need' => '0',
            'preferred_scholarship_type' => 'partial',
            'open_to_abroad' => '0',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'campus' => 'Bandung Institute of Technology',
            'has_financial_need' => 0,
            'preferred_scholarship_type' => 'partial',
            'open_to_abroad' => 0,
        ]);
    }

    public function test_profile_page_displays_saved_student_information(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'campus' => 'University of Indonesia',
            'major' => 'Computer Science',
            'gpa' => 3.75,
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Student Information')
            ->assertSee('University of Indonesia')
            ->assertSee('Computer Science')
            ->assertSee('3.75');
    }

    public function test_profile_details_must_match_database_constraints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'gpa' => 4.1,
                'semester' => 0,
                'preferred_scholarship_type' => 'unlimited',
            ])
            ->assertSessionHasErrors(['gpa', 'semester', 'preferred_scholarship_type'])
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
