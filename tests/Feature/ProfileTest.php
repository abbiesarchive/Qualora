<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

        $response
            ->assertOk()
            ->assertSee('Student Information')
            ->assertSee('What are you looking for?')
            ->assertSee('Language')
            ->assertSee('Experience')
            ->assertDontSee('Update Password')
            ->assertDontSee('Delete Account')
            ->assertDontSee('id="email"');
    }

    public function test_account_page_contains_account_settings_and_dropdown_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee('Account Information')
            ->assertSee('id="account_name"', false)
            ->assertSee('id="account_email"', false)
            ->assertSee('Update Password')
            ->assertSee('Delete Account')
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('href="'.route('account.edit').'"', false)
            ->assertDontSee('Student Information')
            ->assertDontSee('Study Plan')
            ->assertDontSee('Language')
            ->assertDontSee('Experience');
    }

    public function test_account_information_can_be_updated_from_account_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/account', [
                'name' => 'Updated Account Name',
                'email' => 'updated-account@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/account');

        $this->assertSame('Updated Account Name', $user->refresh()->name);
        $this->assertSame('updated-account@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
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
        ];

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
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
            'age' => 20,
            'nationality' => 'Indonesian',
            'has_financial_need' => 1,
        ]);

        $this->patch('/profile', [
            'name' => $user->name,
            ...$profileData,
            'campus' => 'Bandung Institute of Technology',
            'has_financial_need' => '0',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'campus' => 'Bandung Institute of Technology',
            'has_financial_need' => 0,
        ]);
    }

    public function test_only_requested_profile_columns_are_removed(): void
    {
        $removedColumns = [
            'achievements',
            'english_test_type',
            'english_score',
            'organizations',
            'goals',
            'preferred_scholarship_type',
            'open_to_abroad',
            'circumstances',
        ];

        foreach ($removedColumns as $column) {
            $this->assertFalse(Schema::hasColumn('profiles', $column), "The {$column} column should be removed.");
        }

        $this->assertTrue(Schema::hasColumns('profiles', [
            'id',
            'user_id',
            'campus',
            'major',
            'semester',
            'gpa',
            'age',
            'nationality',
            'has_financial_need',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_legacy_profile_fields_are_ignored_when_updating_remaining_profile_information(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'campus' => 'University of Indonesia',
                'achievements' => "Dean's List",
                'english_test_type' => 'IELTS',
                'english_score' => 7.5,
                'organizations' => 'Student Association',
                'goals' => "Pursue a master's degree",
                'preferred_scholarship_type' => 'full',
                'open_to_abroad' => '1',
                'circumstances' => 'First-generation student',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'campus' => 'University of Indonesia',
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

    public function test_detailed_personal_fields_are_not_collected(): void
    {
        foreach (['faculty', 'gender', 'city_of_residence', 'phone_number', 'graduation_year', 'graduation_status', 'university_accreditation'] as $column) {
            $this->assertFalse(Schema::hasColumn('profiles', $column), "The {$column} column should not exist.");
        }
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
            ])
            ->assertSessionHasErrors(['gpa', 'semester'])
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
            ->delete('/account', [
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
            ->from('/account')
            ->delete('/account', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/account');

        $this->assertNotNull($user->fresh());
    }

    public function test_one_save_also_stores_the_study_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'major' => 'Informatics',
            'study_field_category' => 'Technology & Computing',
            'preferred_country' => 'Europe',
        ])->assertRedirect(route('profile.edit'));

        $profile = $user->fresh()->profile;
        $this->assertSame('Informatics', $profile->major);
        $this->assertSame('Technology & Computing', $profile->studyPlan->study_field_category);
        $this->assertSame('Europe', $profile->studyPlan->preferred_country);
    }

    public function test_saving_with_empty_preferences_does_not_create_an_empty_study_plan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'major' => 'Informatics',
            'study_field_category' => '',
            'preferred_country' => '',
        ]);

        $this->assertNull($user->fresh()->profile->studyPlan);
    }

    public function test_preferences_outside_the_allowed_list_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'preferred_country' => 'Atlantis',
        ])->assertSessionHasErrors('preferred_country');
    }

    public function test_profile_page_has_one_floating_save_button_disabled_until_changes(): void
    {
        $html = $this->actingAs(User::factory()->create())->get(route('profile.edit'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'type="submit" form="profile-form"'));
        $this->assertStringContainsString('x-bind:disabled="!dirty"', $html);
        $this->assertStringContainsString('Leave without saving?', $html);
        $this->assertStringContainsString('forced: false', $html);
    }

    public function test_page_starts_unsaved_after_the_ai_prefills_the_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['ai_prefill' => ['major' => 'Law', 'semester' => 3]])
            ->get(route('profile.edit'))
            ->assertSee('forced: true', false);
    }
}

