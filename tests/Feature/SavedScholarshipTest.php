<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedScholarshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_saved_scholarships_or_bookmark_actions(): void
    {
        $scholarship = $this->createScholarship();

        $this->get(route('scholarships.saved'))->assertRedirect('/login');
        $this->post(route('scholarships.bookmark.store', $scholarship))->assertRedirect('/login');
        $this->delete(route('scholarships.bookmark.destroy', $scholarship))->assertRedirect('/login');
    }

    public function test_user_can_save_and_remove_a_scholarship(): void
    {
        $user = User::factory()->create();
        $scholarship = $this->createScholarship();

        $this->actingAs($user)
            ->get(route('scholarships.index'))
            ->assertOk()
            ->assertSee('aria-pressed="false"', false);

        $this->post(route('scholarships.bookmark.store', $scholarship))
            ->assertRedirect();

        $this->assertDatabaseHas('saved_scholarships', [
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
        ]);

        $this->get(route('scholarships.show', $scholarship))
            ->assertOk()
            ->assertSee('aria-pressed="true"', false);

        $this->delete(route('scholarships.bookmark.destroy', $scholarship))
            ->assertRedirect();

        $this->assertDatabaseMissing('saved_scholarships', [
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
        ]);
    }

    public function test_saved_filter_only_displays_the_current_users_scholarships(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $ownScholarship = $this->createScholarship(['name' => 'My saved scholarship']);
        $otherScholarship = $this->createScholarship(['name' => "Someone else's scholarship"]);

        $user->savedScholarships()->create(['scholarship_id' => $ownScholarship->id]);
        $anotherUser->savedScholarships()->create(['scholarship_id' => $otherScholarship->id]);

        $this->actingAs($user)
            ->get(route('scholarships.index', ['view' => 'saved']))
            ->assertOk()
            ->assertSee('My saved scholarship')
            ->assertDontSee("Someone else's scholarship");
    }

    public function test_legacy_saved_scholarships_url_redirects_to_the_saved_filter(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('scholarships.saved'))
            ->assertRedirect(route('scholarships.index', ['view' => 'saved']));
    }

    public function test_removing_a_bookmark_does_not_remove_another_users_bookmark(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $scholarship = $this->createScholarship();

        $user->savedScholarships()->create(['scholarship_id' => $scholarship->id]);
        $otherSavedScholarship = $anotherUser->savedScholarships()->create([
            'scholarship_id' => $scholarship->id,
        ]);

        $this->actingAs($user)
            ->delete(route('scholarships.bookmark.destroy', $scholarship))
            ->assertRedirect();

        $this->assertDatabaseHas('saved_scholarships', [
            'id' => $otherSavedScholarship->id,
            'user_id' => $anotherUser->id,
            'scholarship_id' => $scholarship->id,
        ]);
    }

    private function createScholarship(array $attributes = []): Scholarship
    {
        return Scholarship::forceCreate($attributes + [
            'name' => 'Test Scholarship '.uniqid(),
            'provider' => 'Test Foundation',
            'level' => 'S1',
            'scholarship_type' => 'full',
            'official_url' => 'https://example.org/apply',
            'last_verified' => today(),
            'year' => 2026,
            'is_active' => true,
        ]);
    }
}
