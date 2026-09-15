<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use App\Support\YouTubeFeed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

const CHANNEL = 'UCabcdefghijklmnopqrstuv';

/**
 * A minimal version of the Atom feed YouTube actually serves.
 */
function feedXml(int $count = 3): string
{
    $entries = '';

    foreach (range(1, $count) as $i) {
        // Video IDs are exactly 11 URL-safe characters.
        $id = str_pad((string) $i, 11, 'a', STR_PAD_LEFT);

        $entries .= '<entry>'
            ."<yt:videoId>{$id}</yt:videoId>"
            ."<title>Video number {$i}</title>"
            .'<published>2026-01-0'.min($i, 9).'T00:00:00+00:00</published>'
            .'</entry>';
    }

    // Concatenated rather than a heredoc: leading whitespace before the XML
    // declaration makes the document unparseable, and an indented heredoc
    // silently introduces it.
    return '<?xml version="1.0" encoding="UTF-8"?>'
        .'<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015" xmlns="http://www.w3.org/2005/Atom">'
        .'<title>Test Channel</title>'
        .$entries
        .'</feed>';
}

/**
 * Point the stored channel at a feed and clear the caches around it.
 */
function useChannel(string $channelId, string $limit = ''): void
{
    $row = ContentSection::query()->section('channel')->first();
    $data = $row->data;
    $data['channel_id'] = $channelId;
    $data['video_limit'] = $limit;
    $row->update(['data' => $data]);

    Content::flush();
    Cache::flush();
}

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    Cache::flush();
});

it('pulls the latest videos from the feed', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response(feedXml(3))]);

    $videos = YouTubeFeed::videos(CHANNEL, 3);

    expect($videos)->toHaveCount(3)
        ->and($videos[0]['title'])->toBe('Video number 1')
        ->and($videos[0]['url'])->toContain('watch?v=')
        ->and($videos[0]['thumbnail'])->toContain('i.ytimg.com');
});

it('shows feed videos on the page', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response(feedXml(3))]);
    useChannel(CHANNEL);

    $this->get('/en')
        ->assertOk()
        ->assertSee('Video number 1')
        ->assertSee('i.ytimg.com', escape: false);
});

it('respects the video limit set in the dashboard', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response(feedXml(10))]);
    useChannel(CHANNEL, '2');

    $response = $this->get('/en')->assertOk();

    $response->assertSee('Video number 2')->assertDontSee('Video number 3');
});

it('caches the feed instead of fetching on every request', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response(feedXml(2))]);
    useChannel(CHANNEL);

    $this->get('/en')->assertOk();
    $this->get('/en')->assertOk();
    $this->get('/en')->assertOk();

    // Three page views, one upstream request.
    Http::assertSentCount(1);
});

it('renders the page normally when the feed is unreachable', function (): void {
    // YouTube being down must never take the site with it.
    Http::fake(['*youtube.com/feeds/*' => Http::response('', 503)]);
    useChannel(CHANNEL);

    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::get('channel')['name']);
});

it('falls back to pinned videos when the feed fails', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response('', 500)]);

    $row = ContentSection::query()->section('channel')->first();
    $data = $row->data;
    $data['channel_id'] = CHANNEL;
    $data['videos'] = [
        ['id' => 'pinnedVideo', 'title' => ['ar' => 'مثبّت', 'en' => 'Pinned video']],
    ];
    $row->update(['data' => $data]);
    Content::flush();
    Cache::flush();

    // The section keeps working from hand-entered entries.
    $this->get('/en')->assertOk()->assertSee('Pinned video');
});

it('ignores a malformed channel ID without making a request', function (string $bad): void {
    Http::fake();

    expect(YouTubeFeed::videos($bad))->toBe([]);

    Http::assertNothingSent();
})->with([
    'empty' => [''],
    'wrong prefix' => ['XXabcdefghijklmnopqrstuv'],
    'too short' => ['UCabc'],
    'a url' => ['https://evil.example/feed'],
    'path traversal' => ['UC../../../etc/passwd'],
]);

it('skips feed entries whose video ID is malformed', function (): void {
    // A bad entry must not become a card pointing at a broken video.
    // Built as one string: an indented heredoc leaves leading whitespace
    // before the XML declaration, which makes the whole document unparseable.
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015" xmlns="http://www.w3.org/2005/Atom">'
        .'<entry><yt:videoId>../../etc</yt:videoId><title>Bad</title></entry>'
        .'<entry><yt:videoId>goodVideoID</yt:videoId><title>Good</title></entry>'
        .'</feed>';

    Http::fake(['*youtube.com/feeds/*' => Http::response($xml)]);

    $videos = YouTubeFeed::videos(CHANNEL, 5);

    expect($videos)->toHaveCount(1)
        ->and($videos[0]['id'])->toBe('goodVideoID');
});

it('survives a feed that is not valid XML', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response('<not xml at all')]);

    expect(YouTubeFeed::videos(CHANNEL))->toBe([]);
});

it('clears the cached feed when the channel is saved', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response(feedXml(1))]);
    useChannel(CHANNEL);

    $this->get('/en')->assertOk();
    expect(Cache::has(YouTubeFeed::cacheKey(CHANNEL)))->toBeTrue();

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'channel'])
        ->call('save')
        ->assertHasNoErrors();

    // A stale list after editing the channel would be confusing.
    expect(Cache::has(YouTubeFeed::cacheKey(CHANNEL)))->toBeFalse();
});

it('requests the feed over https, by channel id', function (): void {
    Http::fake(['*youtube.com/feeds/*' => Http::response(feedXml(1))]);

    YouTubeFeed::videos(CHANNEL);

    Http::assertSent(fn ($request): bool => str_starts_with($request->url(), 'https://www.youtube.com/feeds/videos.xml')
        && str_contains($request->url(), 'channel_id='.CHANNEL));
});
