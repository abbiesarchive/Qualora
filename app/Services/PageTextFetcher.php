<?php

namespace App\Services;

use App\Exceptions\PageFetchException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Admin-only helper: downloads ONE web page the admin typed in and returns its
 * readable text, so it can be pasted into the extraction box for review.
 * It is not a crawler: one URL per click, no link following, no storage.
 *
 * Because the server makes the request, it refuses anything that resolves to a
 * private/internal address (SSRF) and checks every redirect hop the same way.
 */
class PageTextFetcher
{
    private const MAX_REDIRECTS = 3;

    private const MAX_BYTES = 1_500_000;

    private const MIN_TEXT_CHARS = 200;

    /**
     * @throws PageFetchException
     */
    public function fetch(string $url): string
    {
        $body = null;

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            [$host, $ip, $port] = $this->resolveSafely($url);

            try {
                $response = Http::timeout(10)
                    ->withoutRedirecting()
                    ->withHeaders(['User-Agent' => 'Qualora admin importer'])
                    ->withOptions(['curl' => [CURLOPT_RESOLVE => ["$host:$port:$ip"]]])
                    ->get($url);
            } catch (ConnectionException) {
                throw new PageFetchException('Could not reach that page. Check the link, or paste the text instead.');
            }

            if ($response->redirect()) {
                $location = $response->header('Location');
                if ($location === '') {
                    break;
                }
                $url = $this->absolute($url, $location);

                continue;
            }

            if ($response->failed()) {
                throw new PageFetchException('That page returned an error ('.$response->status().'). Paste the text instead.');
            }

            $type = strtolower($response->header('Content-Type'));
            if ($type !== '' && ! str_contains($type, 'text/html') && ! str_contains($type, 'text/plain')) {
                throw new PageFetchException('Only normal web pages can be read here (not PDFs or images). Paste the text instead.');
            }

            $body = substr($response->body(), 0, self::MAX_BYTES);
            break;
        }

        if ($body === null) {
            throw new PageFetchException('That page redirected too many times. Paste the text instead.');
        }

        $text = $this->toText($body);

        if (mb_strlen($text) < self::MIN_TEXT_CHARS) {
            throw new PageFetchException('That page has almost no readable text (it may need JavaScript). Paste the text instead.');
        }

        return mb_substr($text, 0, ScholarshipExtractionService::SOURCE_MAX_CHARS);
    }

    /**
     * @return array{0: string, 1: string, 2: int} host, public IP, port
     *
     * @throws PageFetchException
     */
    private function resolveSafely(string $url): array
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user'])) {
            throw new PageFetchException('Enter a full web address starting with http:// or https://.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);

        if ($ips === []) {
            throw new PageFetchException('Could not find that website. Check the link, or paste the text instead.');
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new PageFetchException('That address is not allowed.');
            }
        }

        return [$host, $ips[0], $parts['port'] ?? ($scheme === 'https' ? 443 : 80)];
    }

    private function absolute(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $p = parse_url($base);
        $origin = $p['scheme'].'://'.$p['host'].(isset($p['port']) ? ':'.$p['port'] : '');

        return str_starts_with($location, '/') ? $origin.$location : $origin.'/'.$location;
    }

    private function toText(string $html): string
    {
        $html = preg_replace('#<(script|style|noscript|svg|nav|footer|header|form)\b.*?</\1>#is', ' ', $html) ?? $html;
        $html = preg_replace('#<(br|/p|/div|/li|/tr|/h[1-6]|/table|/ul|/ol)\b[^>]*>#i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t\x{00A0}]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n[ \n]*/', "\n", $text) ?? $text;

        return trim($text);
    }
}
