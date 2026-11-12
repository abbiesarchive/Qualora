<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_multiple_languages_view_them_and_update_one_in_place(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $this->actingAs($user)
            ->post('/profile/languages', [
                'language_name' => 'English',
                'proficiency_level' => 'Advanced',
                'certificate_type' => 'IELTS',
                'certificate_score' => '8.0',
                'certificate_expiry' => '2028-04-30',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $english = $profile->languages()->firstOrFail();

        $this->post('/profile/languages', [
            'language_name' => 'French',
            'proficiency_level' => 'Intermediate',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('user_language', 2);
        $this->assertDatabaseHas('user_language', [
            'user_language_id' => $english->getKey(),
            'profile_id' => $profile->id,
            'language_name' => 'English',
        ]);

        $this->get('/profile')
            ->assertOk()
            ->assertSee('Language')
            ->assertSee('English')
            ->assertSee('French')
            ->assertSee('IELTS')
            ->assertSee('8.0')
            ->assertSee('2028-04-30');

        $this->patch("/profile/languages/{$english->getKey()}", [
            'language_name' => 'English',
            'proficiency_level' => 'Proficient / Native',
            'profile_id' => $profile->id + 1,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('user_language', 2);
        $this->assertDatabaseHas('user_language', [
            'user_language_id' => $english->getKey(),
            'profile_id' => $profile->id,
            'proficiency_level' => 'Proficient / Native',
        ]);

        $this->delete("/profile/languages/{$english->getKey()}")
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'language-deleted');

        $this->assertDatabaseMissing('user_language', [
            'user_language_id' => $english->getKey(),
        ]);
        $this->assertDatabaseCount('user_language', 1);
    }

    public function test_user_cannot_view_update_or_delete_another_users_language(): void
    {
        $owner = User::factory()->create();
        $ownerProfile = $owner->profile()->create([]);
        $language = $ownerProfile->languages()->create([
            'language_name' => 'Private Language',
        ]);

        $otherUser = User::factory()->create();
        $otherProfile = $otherUser->profile()->create([]);

        $this->actingAs($otherUser)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Private Language');

        $this->post('/profile/languages', [
            'language_name' => 'My Language',
            'profile_id' => $ownerProfile->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->patch("/profile/languages/{$language->getKey()}", [
            'language_name' => 'Changed by another user',
        ])->assertNotFound();

        $this->delete("/profile/languages/{$language->getKey()}")->assertNotFound();

        $this->assertDatabaseHas('user_language', [
            'user_language_id' => $language->getKey(),
            'profile_id' => $ownerProfile->id,
            'language_name' => 'Private Language',
        ]);
        $this->assertDatabaseHas('user_language', [
            'profile_id' => $otherProfile->id,
            'language_name' => 'My Language',
        ]);
    }

    public function test_language_name_is_required_and_proficiency_must_be_a_supported_level(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $this->actingAs($user)
            ->followingRedirects()
            ->from('/profile')
            ->post('/profile/languages', [
                'proficiency_level' => 'Fluentish',
            ])
            ->assertOk()
            ->assertSee('The language name field is required.');

        $this->assertDatabaseCount('user_language', 0);
    }

    public function test_guest_cannot_access_language_operations(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->post('/profile/languages')->assertRedirect('/login');
        $this->patch('/profile/languages/any-id')->assertRedirect('/login');
        $this->delete('/profile/languages/any-id')->assertRedirect('/login');
    }
}
