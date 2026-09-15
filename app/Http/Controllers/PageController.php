<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Content;
use App\Support\YouTubeFeed;
use Illuminate\Contracts\View\View;

/**
 * The site is a single page, so one action assembles every section of it.
 */
final class PageController extends Controller
{
    /** Videos shown when the dashboard does not say otherwise. */
    private const DEFAULT_VIDEOS = 3;

    public function __invoke(): View
    {
        /** @var array<string, mixed> $channel */
        $channel = Content::get('channel', []);

        return view('page', [
            'hero' => Content::get('hero'),
            'stats' => $this->stats(),
            'about' => Content::get('about'),
            'projects' => Content::list('projects'),
            'otherProjects' => Content::list('other_projects'),
            'timeline' => Content::list('timeline'),
            'freelance' => Content::list('freelance'),
            'sideProjects' => Content::list('side_projects'),
            'education' => Content::list('education'),
            'skills' => Content::list('skills'),
            'certificates' => Content::list('certificates'),
            'writing' => Content::list('writing'),
            // Latest posts, teasing the blog from the homepage.
            'posts' => Post::query()->published()->limit(3)->get(),
            'channel' => $channel,
            'videos' => $this->videos($channel),
            'contact' => Content::get('contact'),
            'socials' => Content::list('socials'),
        ]);
    }

    /**
     * The three hero figures.
     *
     * Counts are derived from the content itself rather than typed by hand, so
     * they cannot drift out of date as work is added. Years of experience is
     * the one figure with no countable source, so it stays editable in the
     * dashboard under Hero.
     *
     * @return list<array{value: string, label: string}>
     */
    private function stats(): array
    {
        // Every distinct piece of work, counted once. A project listed both as
        // a case study and as freelance work is still one project.
        $projects = collect(Content::list('projects'))
            ->concat(Content::list('other_projects'))
            ->concat(Content::list('freelance'))
            ->concat(Content::list('side_projects'))
            ->map(fn (array $p): string => mb_strtolower(trim((string) ($p['name'] ?? ''))))
            ->filter()
            ->unique()
            ->count();

        $roles = count(Content::list('timeline'));

        return [
            [
                'value' => Content::string('hero.experience', ''),
                'label' => __('years of experience'),
            ],
            [
                'value' => $this->number($projects).'+',
                'label' => __('projects delivered'),
            ],
            [
                'value' => $this->number($roles),
                'label' => __('companies worked with'),
            ],
        ];
    }

    /**
     * Render a figure in the numerals the active locale reads.
     */
    private function number(int $value): string
    {
        $digits = (string) $value;

        return app()->getLocale() === 'ar'
            ? strtr($digits, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
                '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩'])
            : $digits;
    }

    /**
     * The videos to show for the channel.
     *
     * A channel ID pulls the latest uploads from YouTube's RSS feed, so the
     * list stays current without anyone editing it. Videos pinned by hand in
     * the dashboard are the fallback — used when no channel ID is set, or when
     * the feed is unreachable — so the section never empties out on a bad day.
     *
     * @param  array<string, mixed>  $channel
     * @return list<array{id: string, title: string, url: string, thumbnail: string}>
     */
    private function videos(array $channel): array
    {
        $limit = (int) ($channel['video_limit'] ?? 0);
        $limit = $limit > 0 ? min($limit, 12) : self::DEFAULT_VIDEOS;

        $fromFeed = YouTubeFeed::videos((string) ($channel['channel_id'] ?? ''), $limit);

        if ($fromFeed !== []) {
            return $fromFeed;
        }

        return $this->pinnedVideos($channel, $limit);
    }

    /**
     * Videos entered by hand in the dashboard, normalised into the same shape
     * the feed returns so the view does not care which source it got.
     *
     * @param  array<string, mixed>  $channel
     * @return list<array{id: string, title: string, url: string, thumbnail: string}>
     */
    private function pinnedVideos(array $channel, int $limit): array
    {
        $videos = [];

        foreach ((array) ($channel['videos'] ?? []) as $video) {
            $id = trim((string) ($video['id'] ?? ''));

            if (preg_match('/^[A-Za-z0-9_-]{11}$/', $id) !== 1) {
                continue;
            }

            $videos[] = [
                'id' => $id,
                'title' => (string) ($video['title'] ?? ''),
                'url' => "https://www.youtube.com/watch?v={$id}",
                'thumbnail' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
            ];
        }

        return array_slice($videos, 0, $limit);
    }
}
