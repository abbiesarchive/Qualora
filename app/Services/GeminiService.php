<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The only place that talks to Gemini. Server-side only: the key never reaches
 * the browser. Callers get decoded JSON back and MUST validate it (Validator)
 * before using or saving it. The AI never decides eligibility.
 */
class GeminiService
{
    /** Longest user-supplied text we send, in characters. */
    public const MAX_INPUT_CHARS = 8000;

    /**
     * Ask Gemini for JSON that matches $schema.
     *
     * @param  array<string, mixed>  $schema  JSON Schema (lowercase types, e.g. "object")
     * @return array<string, mixed>
     *
     * @throws GeminiException
     */
    public function generateJson(string $prompt, array $schema, ?string $system = null): array
    {
        $text = $this->generate($prompt, $system, [
            'responseMimeType' => 'application/json',
            'responseJsonSchema' => $schema,
        ]);

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            Log::warning('Gemini returned non-JSON output', ['output' => Str::limit($text, 500)]);
            throw new GeminiException(__('The AI gave an unexpected answer. Please try again.'));
        }

        return $decoded;
    }

    /**
     * Trim untrusted text to the allowed length before it goes into a prompt.
     */
    public function limitInput(string $text): string
    {
        return Str::limit(trim($text), self::MAX_INPUT_CHARS, '');
    }

    /**
     * @param  array<string, mixed>  $generationConfig
     *
     * @throws GeminiException
     */
    private function generate(string $prompt, ?string $system, array $generationConfig): string
    {
        $key = config('services.gemini.key');

        if (! $key) {
            throw new GeminiException(__('The AI helper is not set up right now. You can still fill in the form by hand.'));
        }

        $body = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => $generationConfig + ['temperature' => 0.2],
        ];

        if ($system !== null) {
            $body['systemInstruction'] = ['parts' => [['text' => $system]]];
        }

        $url = rtrim(config('services.gemini.base_url'), '/')
            .'/models/'.config('services.gemini.model').':generateContent';

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->timeout(config('services.gemini.timeout'))
                // Retry twice, only when Google says "too many requests" (429).
                ->retry(2, 2000, fn ($e) => $e instanceof RequestException && $e->response->status() === 429, throw: false)
                ->post($url, $body);
        } catch (ConnectionException $e) {
            Log::warning('Gemini connection failed', ['error' => $e->getMessage()]);
            throw new GeminiException(__('Could not reach the AI helper. Please try again in a moment.'));
        }

        return $this->extractText($response);
    }

    /**
     * @throws GeminiException
     */
    private function extractText(Response $response): string
    {
        if ($response->status() === 429) {
            throw new GeminiException(__('The AI helper is busy right now. Please wait a minute and try again.'));
        }

        if ($response->failed()) {
            // Log the detail, but never put it (or the key) in front of the user.
            Log::warning('Gemini request failed', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 500),
            ]);
            throw new GeminiException(__('The AI helper had a problem. Please try again.'));
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $text = collect($parts)->pluck('text')->filter()->implode('');

        if ($text === '') {
            Log::warning('Gemini returned no text', ['finish' => $response->json('candidates.0.finishReason')]);
            throw new GeminiException(__('The AI did not return an answer. Please try again.'));
        }

        return $text;
    }
}
