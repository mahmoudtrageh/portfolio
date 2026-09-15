<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reads a channel's latest uploads from YouTube's public RSS feed.
 *
 * The feed needs no API key and updates itself when a video is published, so
 * the site never shows a stale hand-typed list.
 *
 * Failure is always silent: the page renders whatever else it has rather than
 * breaking because YouTube is slow, rate-limiting, or down. A failed fetch is
 * cached briefly too, so an outage does not mean a blocking request on every
 * single page view.
 */
final class YouTubeFeed
{
    /** How long a good response is reused. */
    private const TTL_MINUTES = 180;

    /** How long a failure is remembered, so an outage is not retried per request. */
    private const FAILURE_TTL_MINUTES = 10;

    /** Requests are capped tightly: this is a page render, not a background job. */
    private const TIMEOUT_SECONDS = 4;

    /**
     * The latest videos for a channel, newest first.
     *
     * @return list<array{id: string, title: string, url: string, thumbnail: string, published: string}>
     */
    public static function videos(string $channelId, int $limit = 3): array
    {
        $channelId = trim($channelId);

        if (! self::isValidChannelId($channelId) || $limit < 1) {
            return [];
        }

        /** @var list<array{id: string, title: string, url: string, thumbnail: string, published: string}> $videos */
        $videos = Cache::remember(
            self::cacheKey($channelId),
            now()->addMinutes(self::TTL_MINUTES),
            fn (): array => self::fetch($channelId),
        );

        return array_slice($videos, 0, $limit);
    }

    /**
     * A channel ID is "UC" plus 22 URL-safe base64 characters. Validated before
     * use so a mistyped value cannot build a request to an arbitrary URL.
     */
    public static function isValidChannelId(string $channelId): bool
    {
        return preg_match('/^UC[A-Za-z0-9_-]{22}$/', $channelId) === 1;
    }

    public static function cacheKey(string $channelId): string
    {
        return "youtube.feed.{$channelId}";
    }

    /**
     * Drop the cached feed, so a dashboard save shows new videos immediately.
     */
    public static function flush(string $channelId): void
    {
        Cache::forget(self::cacheKey($channelId));
    }

    /**
     * @return list<array{id: string, title: string, url: string, thumbnail: string, published: string}>
     */
    private static function fetch(string $channelId): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->retry(1, 200)
                ->get('https://www.youtube.com/feeds/videos.xml', ['channel_id' => $channelId]);

            if (! $response->successful()) {
                return self::rememberFailure($channelId, "HTTP {$response->status()}");
            }

            return self::parse($response->body());
        } catch (Throwable $e) {
            return self::rememberFailure($channelId, $e->getMessage());
        }
    }

    /**
     * Cache an empty result briefly and log why, so a broken feed shows up in
     * the logs rather than only as a silently missing section.
     *
     * @return list<never>
     */
    private static function rememberFailure(string $channelId, string $reason): array
    {
        Log::warning('YouTube feed unavailable', ['channel' => $channelId, 'reason' => $reason]);

        Cache::put(self::cacheKey($channelId), [], now()->addMinutes(self::FAILURE_TTL_MINUTES));

        return [];
    }

    /**
     * Parse the Atom feed YouTube returns.
     *
     * @return list<array{id: string, title: string, url: string, thumbnail: string, published: string}>
     */
    private static function parse(string $xml): array
    {
        if (trim($xml) === '') {
            return [];
        }

        // The feed is a fixed public document, but it is still third-party
        // input: parse it without resolving external entities.
        $previous = libxml_use_internal_errors(true);

        try {
            $feed = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOENT);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($feed === false) {
            return [];
        }

        $videos = [];

        foreach ($feed->entry ?? [] as $entry) {
            $yt = $entry->children('http://www.youtube.com/xml/schemas/2015');
            $id = trim((string) $yt->videoId);

            // A malformed entry is skipped rather than rendered as a broken card.
            if ($id === '' || preg_match('/^[A-Za-z0-9_-]{11}$/', $id) !== 1) {
                continue;
            }

            $videos[] = [
                'id' => $id,
                'title' => trim((string) $entry->title),
                'url' => "https://www.youtube.com/watch?v={$id}",
                'thumbnail' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
                'published' => trim((string) $entry->published),
            ];
        }

        return $videos;
    }
}
