<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

/**
 * RSS feed of the blog, one per locale.
 */
final class FeedController extends Controller
{
    private const LIMIT = 20;

    public function __invoke(string $locale): Response
    {
        $posts = Post::query()->published($locale)->limit(self::LIMIT)->get();

        return response()
            ->view('feed', ['posts' => $posts, 'locale' => $locale])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
