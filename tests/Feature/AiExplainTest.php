<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiExplainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-key']);
    }

    private function scholarship(): Scholarship
    {
        return Scholarship::forceCreate([
            'name' => 'Test Scholarship '.uniqid(),
            'provider' => 'Test Foundation',
            'level' => 'S1',
            'min_gpa' => 3.25,
            'scholarship_type' => 'full',
            'official_url' => 'https://example.org/apply',
            'last_verified' => today(),
            'year' => 2026,
            'is_active' => true,
        ]);
    }

    private function reply(array $payload): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]]];
    }

    private function good(): array
    {
        return $this->reply([
            'explanation' => 'This scholarship supports students. Its GPA rule is stated; verify on the official site.',
            'checklist' => ['Prepare your transcript', 'Submit before the deadline'],
        ]);
    }

    public function test_guests_cannot_call_the_endpoint(): void
    {
        Http::fake();

        $this->post(route('ai.explain'), ['scholarship_id' => 1])->assertRedirect(route('login'));
        Http::assertNothingSent();
    }

    public function test_generates_validates_and_caches_the_explanation(): void
    {
        Http::fake(['*' => Http::response($this->good())]);
        $s = $this->scholarship();

        $this->actingAs(User::factory()->create())
            ->post(route('ai.explain'), ['scholarship_id' => $s->id])
            ->assertRedirect();

        $s->refresh();
        $this->assertStringContainsString('verify on the official site', $s->ai_explanation);
        $this->assertSame(['Prepare your transcript', 'Submit before the deadline'], $s->ai_checklist);
    }

    public function test_uses_the_cache_instead_of_calling_the_ai_again(): void
    {
        Http::fake();
        $s = $this->scholarship();
        $s->forceFill(['ai_explanation' => 'Cached explanation text here.', 'ai_checklist' => ['Step one']])->save();

        $this->actingAs(User::factory()->create())->post(route('ai.explain'), ['scholarship_id' => $s->id]);

        Http::assertNothingSent();
    }

    public function test_invalid_ai_output_is_rejected_and_never_saved(): void
    {
        Http::fake(['*' => Http::response($this->reply(['explanation' => 'short', 'checklist' => []]))]);
        $s = $this->scholarship();

        $this->actingAs(User::factory()->create())
            ->post(route('ai.explain'), ['scholarship_id' => $s->id])
            ->assertSessionHas('ai_error');

        $this->assertNull($s->fresh()->ai_explanation);
    }

    public function test_only_stored_data_is_sent_to_the_ai_and_missing_values_are_not_stated(): void
    {
        Http::fake(['*' => Http::response($this->good())]);
        $s = $this->scholarship();
        $s->forceFill(['min_gpa' => null, 'other_requirements' => null])->save();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('ai.explain'), ['scholarship_id' => $s->id, 'name' => 'Injected']);

        Http::assertSent(function ($request) use ($s, $user) {
            $prompt = $request['contents'][0]['parts'][0]['text'];

            return str_contains($prompt, $s->name)
                && str_contains($prompt, '"min_gpa": "not stated"')
                && ! str_contains($prompt, 'Injected')
                && ! str_contains($prompt, $user->email);
        });
    }

    public function test_unknown_scholarship_is_rejected(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('ai.explain'), ['scholarship_id' => 99999])
            ->assertSessionHasErrors('scholarship_id');
        Http::assertNothingSent();
    }

    public function test_ai_failure_shows_a_friendly_message(): void
    {
        Http::fake(['*' => Http::response([], 500)]);
        $s = $this->scholarship();

        $this->actingAs(User::factory()->create())
            ->post(route('ai.explain'), ['scholarship_id' => $s->id])
            ->assertSessionHas('ai_error');
    }
}
