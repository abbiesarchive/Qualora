<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_multiple_experiences_view_them_and_update_one_in_place(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $this->actingAs($user)
            ->post('/profile/experiences', [
                'experience_type' => 'Internship',
                'experience_name' => 'Software Engineering Intern',
                'position' => 'Engineering Intern',
                'organization_name' => 'Qualora',
                'description' => 'Built application features.',
                'start_date' => '2025-01-10',
                'end_date' => '2025-06-30',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $internship = $profile->experiences()->firstOrFail();

        $this->post('/profile/experiences', [
            'experience_type' => 'Project',
            'experience_name' => 'Open Source Project',
            'start_date' => '2025-03-01',
            'end_date' => null,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('user_experiences', 2);
        $this->assertDatabaseHas('user_experiences', [
            'user_experiences_id' => $internship->getKey(),
            'profile_id' => $profile->id,
            'experience_name' => 'Software Engineering Intern',
            'position' => 'Engineering Intern',
            'organization_name' => 'Qualora',
        ]);
        $this->assertDatabaseHas('user_experiences', [
            'profile_id' => $profile->id,
            'experience_name' => 'Open Source Project',
            'end_date' => null,
        ]);

        $this->get('/profile')
            ->assertOk()
            ->assertSee('Experience')
            ->assertSee('Add Experience')
            ->assertSee('<table', false)
            ->assertSee('<th', false)
            ->assertSee('Edit')
            ->assertSee('x-on:click="startEdit($event.currentTarget.dataset)"', false)
            ->assertSee('data-update-url="'.route('profile.experiences.update', $internship->getKey()).'"', false)
            ->assertSee('Software Engineering Intern')
            ->assertSee('Engineering Intern')
            ->assertSee('Qualora')
            ->assertSee('Built application features.')
            ->assertSee('2025-01-10')
            ->assertSee('2025-06-30')
            ->assertSee('Open Source Project')
            ->assertSee('Present');

        $this->patch("/profile/experiences/{$internship->getKey()}", [
            'experience_type' => 'Work Experience',
            'experience_name' => 'Software Engineer',
            'profile_id' => $profile->id + 1,
            'start_date' => '2025-01-10',
            'end_date' => '2025-07-01',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('user_experiences', 2);
        $this->assertDatabaseHas('user_experiences', [
            'user_experiences_id' => $internship->getKey(),
            'profile_id' => $profile->id,
            'experience_name' => 'Software Engineer',
            'experience_type' => 'Work Experience',
        ]);

        $this->delete("/profile/experiences/{$internship->getKey()}")
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'experience-deleted');

        $this->assertDatabaseMissing('user_experiences', [
            'user_experiences_id' => $internship->getKey(),
        ]);
        $this->assertDatabaseCount('user_experiences', 1);
    }

    public function test_user_cannot_view_update_or_delete_another_users_experience(): void
    {
        $owner = User::factory()->create();
        $ownerProfile = $owner->profile()->create([]);
        $experience = $ownerProfile->experiences()->create([
            'experience_name' => 'Private Experience',
        ]);

        $otherUser = User::factory()->create();
        $otherProfile = $otherUser->profile()->create([]);

        $this->actingAs($otherUser)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Private Experience');

        $this->post('/profile/experiences', [
            'experience_name' => 'My Experience',
            'profile_id' => $ownerProfile->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->patch("/profile/experiences/{$experience->getKey()}", [
            'experience_name' => 'Changed by another user',
        ])->assertNotFound();

        $this->delete("/profile/experiences/{$experience->getKey()}")->assertNotFound();

        $this->assertDatabaseHas('user_experiences', [
            'user_experiences_id' => $experience->getKey(),
            'profile_id' => $ownerProfile->id,
            'experience_name' => 'Private Experience',
        ]);
        $this->assertDatabaseHas('user_experiences', [
            'profile_id' => $otherProfile->id,
            'experience_name' => 'My Experience',
        ]);
    }

    public function test_experience_name_is_required_type_is_validated_and_end_date_cannot_precede_start_date(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $this->actingAs($user)
            ->followingRedirects()
            ->from('/profile')
            ->post('/profile/experiences', [
                'experience_type' => 'Invalid Type',
                'start_date' => '2025-05-01',
                'end_date' => '2025-04-30',
            ])
            ->assertOk()
            ->assertSee('The experience name field is required.')
            ->assertSee('The selected experience type is invalid.')
            ->assertSee('The end date field must be a date after or equal to start date.');

        $this->assertDatabaseCount('user_experiences', 0);
    }

    public function test_guest_cannot_access_experience_operations(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->post('/profile/experiences')->assertRedirect('/login');
        $this->patch('/profile/experiences/any-id')->assertRedirect('/login');
        $this->delete('/profile/experiences/any-id')->assertRedirect('/login');
    }
}
