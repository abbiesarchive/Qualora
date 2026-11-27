<?php

namespace App\Services;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends "something changed" hints to open browser tabs through Supabase Realtime Broadcast.
 *
 * Messages are only hints: they carry no personal data, and the page always re-fetches
 * the real data from our own routes. Public channels can be written to by anyone holding
 * the publishable key, so the browser must never trust a payload's content.
 *
 * Messages are batched and sent after the response (defer), and a failure is only logged,
 * so a Supabase outage never breaks saving a scholarship.
 */
class RealtimeBroadcaster
{
    public const CATALOG_TOPIC = 'qualora-scholarships';

    /** @var list<array{topic: string, event: string, payload: array<string, mixed>}> */
    private array $pending = [];

    public function enabled(): bool
    {
        return filled(config('services.supabase.url')) && filled(config('services.supabase.publishable_key'));
    }

    /** @param 'created'|'updated'|'deleted' $action */
    public function scholarshipChanged(Scholarship $scholarship, string $action): void
    {
        $this->queue(self::CATALOG_TOPIC, 'scholarship_changed', [
            'id' => $scholarship->getKey(),
            'action' => $action,
        ]);
    }

    public function savedScholarshipsChanged(User $user): void
    {
        $this->queue(self::userTopic($user), 'saved_changed', []);
    }

    /**
     * Per-user channel name. It is an HMAC of the user id, so it cannot be guessed
     * from the id, and only the user's own pages are given it.
     */
    public static function userTopic(User $user): string
    {
        return 'qualora-user-'.substr(hash_hmac('sha256', 'saved:'.$user->getKey(), (string) config('app.key')), 0, 32);
    }

    /** @param array<string, mixed> $payload */
    private function queue(string $topic, string $event, array $payload): void
    {
        if (! $this->enabled()) {
            return;
        }

        $isFirst = $this->pending === [];
        $this->pending[] = ['topic' => $topic, 'event' => $event, 'payload' => $payload];

        // One deferred send per request, after the response, carrying every queued message.
        if ($isFirst) {
            defer(fn () => $this->flush());
        }
    }

    public function flush(): void
    {
        $messages = $this->pending;
        $this->pending = [];

        if ($messages === []) {
            return;
        }

        try {
            $response = Http::withHeaders(['apikey' => config('services.supabase.publishable_key')])
                ->acceptJson()
                ->timeout((int) config('services.supabase.realtime_timeout', 3))
                ->post(rtrim((string) config('services.supabase.url'), '/').'/realtime/v1/api/broadcast', [
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                Log::warning('Realtime broadcast failed', ['status' => $response->status()]);
            }
        } catch (Throwable $e) {
            Log::warning('Realtime broadcast failed', ['error' => $e->getMessage()]);
        }
    }
}
