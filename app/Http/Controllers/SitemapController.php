<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Content;
use App\Support\Settings;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];
        $blog = Content::blogEnabled();
        $caseStudies = Settings::caseStudiesEnabled();

        foreach (Settings::activeLocales() as $locale) {
            // The site itself is one page per locale; anchors are not entries.
            $urls[] = [
                'loc' => route('home', ['locale' => $locale]),
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ];

            if ($blog) {
                $urls[] = [
                    'loc' => route('blog', ['locale' => $locale]),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }

            // Projects with a written case study have pages of their own.
            app()->setLocale($locale);

            foreach (Content::list('projects') as $project) {
                // Never advertise a URL that 404s: with case studies switched
                // off, the project pages are gone.
                if (! $caseStudies || ! ProjectController::hasCaseStudy($project)) {
                    continue;
                }

                $urls[] = [
                    'loc' => route('project', ['locale' => $locale, 'slug' => $project['slug']]),
                    'changefreq' => 'yearly',
                    'priority' => '0.7',
                ];
            }

            $posts = $blog ? Post::query()->published($locale)->get() : collect();

            foreach ($posts as $post) {
                $urls[] = [
                    'loc' => route('post', ['locale' => $locale, 'slug' => $post->slug($locale)]),
                    // lastmod tells a crawler whether a re-fetch is worthwhile.
                    'lastmod' => ($post->updated_at ?? $post->published_at)->toAtomString(),
                    'changefreq' => 'yearly',
                    'priority' => '0.6',
                ];
            }
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
