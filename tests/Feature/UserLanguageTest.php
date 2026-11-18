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
                'certificate_type' => 'IELTS',
                'certificate_score' => '8.0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $english = $profile->languages()->firstOrFail();

        $this->post('/profile/languages', [
            'language_name' => 'French',
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
            ->assertSee('Add Language')
            ->assertSee('<table', false)
            ->assertSee('<th', false)
            ->assertSee('Score')
            ->assertSee('Edit')
            ->assertSee('x-on:click="startEdit($event.currentTarget.dataset)"', false)
            ->assertSee('data-update-url="'.route('profile.languages.update', $english->getKey()).'"', false)
            ->assertSee('English')
            ->assertSee('French')
            ->assertSee('IELTS')
            ->assertSee('8.0');

        $this->patch("/profile/languages/{$english->getKey()}", [
            'language_name' => 'English',
            'certificate_type' => 'TOEFL iBT',
            'certificate_score' => '100',
            'profile_id' => $profile->id + 1,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('user_language', 2);
        $this->assertDatabaseHas('user_language', [
            'user_language_id' => $english->getKey(),
            'profile_id' => $profile->id,
            'certificate_type' => 'TOEFL iBT',
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
            'language_name' => 'German',
        ]);

        $otherUser = User::factory()->create();
        $otherProfile = $otherUser->profile()->create([]);

        $this->actingAs($otherUser)
            ->get('/profile')
            ->assertOk()
            ->assertViewHas('languages', fn ($languages) => $languages->isEmpty());

        $this->post('/profile/languages', [
            'language_name' => 'Korean',
            'profile_id' => $ownerProfile->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->patch("/profile/languages/{$language->getKey()}", [
            'language_name' => 'Arabic',
        ])->assertNotFound();

        $this->delete("/profile/languages/{$language->getKey()}")->assertNotFound();

        $this->assertDatabaseHas('user_language', [
            'user_language_id' => $language->getKey(),
            'profile_id' => $ownerProfile->id,
            'language_name' => 'German',
        ]);
        $this->assertDatabaseHas('user_language', [
            'profile_id' => $otherProfile->id,
            'language_name' => 'Korean',
        ]);
    }

    public function test_language_name_is_required_and_values_must_come_from_the_dropdowns(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $this->actingAs($user)
            ->followingRedirects()
            ->from('/profile')
            ->post('/profile/languages', [
                'certificate_type' => 'Made Up Test',
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
