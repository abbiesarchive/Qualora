<?php

namespace Tests\Feature;

use App\Exceptions\GeminiException;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'test-model',
        ]);
    }

    private function reply(string $text): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]];
    }

    private function service(): GeminiService
    {
        return app(GeminiService::class);
    }

    public function test_returns_decoded_json_and_sends_key_model_and_schema(): void
    {
        Http::fake(['*' => Http::response($this->reply('{"gpa":3.4}'))]);

        $schema = ['type' => 'object', 'properties' => ['gpa' => ['type' => 'number']]];
        $result = $this->service()->generateJson('Extract', $schema, 'Never guess.');

        $this->assertSame(['gpa' => 3.4], $result);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/models/test-model:generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && $request['generationConfig']['responseMimeType'] === 'application/json'
            && $request['generationConfig']['responseJsonSchema'] === $schema
            && $request['systemInstruction']['parts'][0]['text'] === 'Never guess.');
    }

    public function test_fails_gracefully_when_no_key_is_configured(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->expectException(GeminiException::class);

        try {
            $this->service()->generateJson('x', []);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_retries_on_429_and_then_succeeds(): void
    {
        Http::fakeSequence()
            ->push([], 429)
            ->push($this->reply('{"ok":true}'));

        $this->assertSame(['ok' => true], $this->service()->generateJson('x', []));
    }

    public function test_shows_friendly_error_after_repeated_429s(): void
    {
        Http::fake(['*' => Http::response([], 429)]);

        $this->expectException(GeminiException::class);
        $this->expectExceptionMessage('busy');

        $this->service()->generateJson('x', []);
    }

    public function test_rejects_output_that_is_not_json(): void
    {
        Http::fake(['*' => Http::response($this->reply('sorry, here is some prose'))]);

        $this->expectException(GeminiException::class);

        $this->service()->generateJson('x', []);
    }

    public function test_never_leaks_provider_error_detail_to_the_user(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'API key test-key invalid']], 400)]);

        try {
            $this->service()->generateJson('x', []);
            $this->fail('Expected GeminiException');
        } catch (GeminiException $e) {
            $this->assertStringNotContainsString('test-key', $e->getMessage());
        }
    }

    public function test_limits_input_length(): void
    {
        $long = str_repeat('a', GeminiService::MAX_INPUT_CHARS + 500);

        $this->assertSame(GeminiService::MAX_INPUT_CHARS, strlen($this->service()->limitInput($long)));
    }
}
