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
            'faculty' => 'Faculty of Computer Science',
            'gender' => 'Prefer not to say',
            'city_of_residence' => 'Depok',
            'phone_number' => '+628123456789',
            'graduation_year' => 2027,
            'graduation_status' => 'In progress',
            'university_accreditation' => 'Excellent',
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
            'age' => 20,
            'nationality' => 'Indonesian',
            'has_financial_need' => 1,
            'faculty' => 'Faculty of Computer Science',
            'gender' => 'Prefer not to say',
            'city_of_residence' => 'Depok',
            'phone_number' => '+628123456789',
            'graduation_year' => 2027,
            'graduation_status' => 'In progress',
            'university_accreditation' => 'Excellent',
        ]);

        $this->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
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
            'faculty',
            'gender',
            'city_of_residence',
            'phone_number',
            'graduation_year',
            'graduation_status',
            'university_accreditation',
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

    public function test_new_profile_fields_are_optional(): void
    {
        $columns = collect(Schema::getColumns('profiles'))->keyBy('name');

        foreach ([
            'faculty',
            'gender',
            'city_of_residence',
            'phone_number',
            'graduation_year',
            'graduation_status',
            'university_accreditation',
        ] as $column) {
            $this->assertTrue($columns[$column]['nullable'], "The {$column} column should be nullable.");
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
