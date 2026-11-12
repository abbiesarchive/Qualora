<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStudyPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_view_and_update_their_single_study_plan(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $this->actingAs($user)
            ->post('/profile/study-plan', [
                'desired_major' => 'Computer Science',
                'preferred_country' => 'Canada',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $studyPlanId = $profile->studyPlan()->value('user_study_plans_id');

        $this->assertDatabaseHas('user_study_plans', [
            'user_study_plans_id' => $studyPlanId,
            'profile_id' => $profile->id,
            'desired_major' => 'Computer Science',
            'preferred_country' => 'Canada',
        ]);

        $this->get('/profile')
            ->assertOk()
            ->assertSee('Study Plan')
            ->assertSee('Computer Science')
            ->assertSee('Canada')
            ->assertDontSee('Delete Study Plan');

        $this->patch("/profile/study-plan/{$studyPlanId}", [
            'desired_major' => 'Data Science',
            'preferred_country' => 'Japan',
            'profile_id' => $profile->id + 1,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('user_study_plans', 1);
        $this->assertDatabaseHas('user_study_plans', [
            'user_study_plans_id' => $studyPlanId,
            'profile_id' => $profile->id,
            'desired_major' => 'Data Science',
            'preferred_country' => 'Japan',
        ]);

        $this->post('/profile/study-plan', [
            'desired_major' => 'A second plan',
        ])
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'study-plan-already-exists');

        $this->assertDatabaseCount('user_study_plans', 1);
        $this->assertDatabaseHas('user_study_plans', [
            'user_study_plans_id' => $studyPlanId,
            'desired_major' => 'Data Science',
        ]);

        $this->delete("/profile/study-plan/{$studyPlanId}")
            ->assertRedirect('/profile');

        $this->assertDatabaseMissing('user_study_plans', [
            'user_study_plans_id' => $studyPlanId,
        ]);
    }

    public function test_user_cannot_view_update_or_delete_another_users_study_plan(): void
    {
        $owner = User::factory()->create();
        $ownerProfile = $owner->profile()->create([]);
        $ownerPlan = $ownerProfile->studyPlan()->create([
            'desired_major' => 'Private Major',
        ]);

        $otherUser = User::factory()->create();
        $otherProfile = $otherUser->profile()->create([]);

        $this->actingAs($otherUser)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Private Major');

        $this->post('/profile/study-plan', [
            'desired_major' => 'Other user plan',
            'profile_id' => $ownerProfile->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->patch("/profile/study-plan/{$ownerPlan->getKey()}", [
            'desired_major' => 'Changed by another user',
        ])->assertNotFound();

        $this->delete("/profile/study-plan/{$ownerPlan->getKey()}")->assertNotFound();

        $this->assertDatabaseHas('user_study_plans', [
            'profile_id' => $ownerProfile->id,
            'desired_major' => 'Private Major',
        ]);
        $this->assertDatabaseHas('user_study_plans', [
            'profile_id' => $otherProfile->id,
            'desired_major' => 'Other user plan',
        ]);
    }

    public function test_guest_cannot_access_study_plan_operations(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->post('/profile/study-plan')->assertRedirect('/login');
        $this->patch('/profile/study-plan/any-id')->assertRedirect('/login');
        $this->delete('/profile/study-plan/any-id')->assertRedirect('/login');
    }

    public function test_user_can_create_a_study_plan_when_their_profile_does_not_exist_yet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/study-plan', [])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('user_study_plans', [
            'profile_id' => $user->profile()->value('id'),
        ]);
    }
}
