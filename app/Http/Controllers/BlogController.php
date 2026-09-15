<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Markdown;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BlogController extends Controller
{
    private const PER_PAGE = 9;

    /**
     * The blog index for the active locale.
     */
    public function index(): View
    {
        return view('blog.index', [
            'posts' => Post::query()
                ->published()
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
        ]);
    }

    /**
     * A single post, looked up by its slug in the active locale.
     */
    public function show(string $locale, string $slug): View
    {
        $post = Post::query()
            ->published($locale)
            ->where("slug_{$locale}", $slug)
            ->first();

        if ($post === null) {
            throw new NotFoundHttpException;
        }

        return view('blog.show', [
            'post' => $post,
            'html' => Markdown::toHtml($post->body($locale)),
            // Newer and older neighbours, so a reader has somewhere to go next.
            'newer' => $this->neighbour($post, $locale, newer: true),
            'older' => $this->neighbour($post, $locale, newer: false),
        ]);
    }

    /**
     * The post published either side of this one, in the same locale.
     */
    private function neighbour(Post $post, string $locale, bool $newer): ?Post
    {
        return Post::query()
            ->published($locale)
            ->where('id', '!=', $post->id)
            ->when(
                $newer,
                fn ($q) => $q->where('published_at', '>', $post->published_at)
                    ->reorder('published_at'),
                fn ($q) => $q->where('published_at', '<', $post->published_at)
                    ->reorder('published_at', 'desc'),
            )
            ->first();
    }
}
