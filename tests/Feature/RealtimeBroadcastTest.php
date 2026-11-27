<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use App\Services\RealtimeBroadcaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RealtimeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private const BROADCAST_URL = 'https://demo.supabase.co/realtime/v1/api/broadcast';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.supabase.url' => 'https://demo.supabase.co',
            'services.supabase.publishable_key' => 'sb_publishable_test',
        ]);

        // Run deferred callbacks straight away so the broadcast happens inside the test.
        $this->withoutDefer();
        Http::fake([self::BROADCAST_URL => Http::response(['message' => 'ok'], 202)]);
    }

    public function test_admin_creating_updating_and_deleting_a_scholarship_notifies_open_pages(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.scholarships.store'), $this->validScholarship())->assertRedirect();
        $scholarship = Scholarship::where('name', 'Live Grant')->firstOrFail();

        $this->put(route('admin.scholarships.update', $scholarship), $this->validScholarship([
            'deadline' => today()->addDays(10)->toDateString(),
            'is_active' => '0',
        ]))->assertRedirect();

        $this->delete(route('admin.scholarships.destroy', $scholarship))->assertRedirect();

        $this->assertSame(
            [
                ['topic' => 'qualora-scholarships', 'event' => 'scholarship_changed', 'payload' => ['id' => $scholarship->id, 'action' => 'created']],
                ['topic' => 'qualora-scholarships', 'event' => 'scholarship_changed', 'payload' => ['id' => $scholarship->id, 'action' => 'updated']],
                ['topic' => 'qualora-scholarships', 'event' => 'scholarship_changed', 'payload' => ['id' => $scholarship->id, 'action' => 'deleted']],
            ],
            $this->sentMessages(),
        );

        Http::assertSent(fn (Request $request) => $request->hasHeader('apikey', 'sb_publishable_test'));
    }

    public function test_caching_an_ai_explanation_does_not_notify_every_open_page(): void
    {
        $scholarship = $this->createScholarship();
        Http::fake([self::BROADCAST_URL => Http::response([], 202)]);
        $this->resetSent();

        $scholarship->update(['ai_explanation' => 'Cached text', 'ai_checklist' => ['Step one']]);
        app(RealtimeBroadcaster::class)->flush();

        Http::assertNothingSent();
    }

    public function test_saving_and_removing_notifies_only_the_users_private_channel_without_personal_data(): void
    {
        $user = User::factory()->create();
        $scholarship = $this->createScholarship();
        $this->resetSent();

        $this->actingAs($user)->post(route('scholarships.bookmark.store', $scholarship))->assertRedirect();
        // Saving the same scholarship again changes nothing, so it sends nothing.
        $this->post(route('scholarships.bookmark.store', $scholarship))->assertRedirect();
        $this->delete(route('scholarships.bookmark.destroy', $scholarship))->assertRedirect();

        $topic = RealtimeBroadcaster::userTopic($user);
        $this->assertSame(
            [
                ['topic' => $topic, 'event' => 'saved_changed', 'payload' => []],
                ['topic' => $topic, 'event' => 'saved_changed', 'payload' => []],
            ],
            $this->sentMessages(),
        );

        // An unguessable hash, not the plain user id.
        $this->assertMatchesRegularExpression('/^qualora-user-[0-9a-f]{32}$/', $topic);
        $this->assertNotSame($topic, RealtimeBroadcaster::userTopic(User::factory()->create()));
    }

    public function test_a_supabase_outage_never_breaks_saving_a_scholarship(): void
    {
        Http::fake([self::BROADCAST_URL => Http::failedConnection()]);
        $this->actingAs($this->admin());

        $this->post(route('admin.scholarships.store'), $this->validScholarship())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('scholarships', ['name' => 'Live Grant']);
    }

    public function test_nothing_is_sent_and_no_keys_are_rendered_when_realtime_is_not_configured(): void
    {
        config(['services.supabase.url' => null, 'services.supabase.publishable_key' => null]);
        $this->resetSent();

        $user = User::factory()->create();
        $scholarship = $this->createScholarship();
        $this->actingAs($user)->post(route('scholarships.bookmark.store', $scholarship));

        Http::assertNothingSent();
        $this->get(route('scholarships.index'))->assertOk()->assertDontSee('supabase-url', false);
    }

    public function test_scholarship_pages_get_the_publishable_key_and_the_users_own_channel(): void
    {
        $user = User::factory()->create();
        $scholarship = $this->createScholarship();

        $this->actingAs($user)->get(route('scholarships.show', $scholarship))
            ->assertOk()
            ->assertSee('<meta name="supabase-publishable-key" content="sb_publishable_test">', false)
            ->assertSee('<meta name="realtime-user-topic" content="'.RealtimeBroadcaster::userTopic($user).'">', false)
            ->assertSee('data-live-page="scholarship" data-scholarship-id="'.$scholarship->id.'"', false);

        $this->get(route('scholarships.index'))->assertOk()->assertSee('data-live-page="catalog"', false);
    }

    /** @return list<array<string, mixed>> */
    private function sentMessages(): array
    {
        return Http::recorded()
            ->flatMap(fn (array $pair) => $pair[0]->data()['messages'] ?? [])
            ->values()
            ->all();
    }

    private function resetSent(): void
    {
        Http::fake([self::BROADCAST_URL => Http::response([], 202)]);
        // A fresh fake keeps earlier recordings, so swap in a clean factory.
        Http::swap(new Factory);
        Http::fake([self::BROADCAST_URL => Http::response([], 202)]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function createScholarship(): Scholarship
    {
        return Scholarship::forceCreate([
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

    private function validScholarship(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Live Grant',
            'provider' => 'Example Foundation',
            'scholarship_type' => 'full',
            'is_abroad' => '0',
            'official_url' => 'https://example.org/apply',
            'last_verified' => today()->toDateString(),
            'year' => today()->year,
            'is_active' => '1',
        ], $overrides);
    }
}
