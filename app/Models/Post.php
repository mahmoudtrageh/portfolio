<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * A blog post, written per locale.
 *
 * Each language has its own slug, title, excerpt, body and publish flag, so a
 * post can go live in Arabic before its English translation exists — and the
 * blog index for a locale never shows a post with nothing to read.
 *
 * @property int $id
 * @property string|null $slug_ar
 * @property string|null $slug_en
 * @property string|null $title_ar
 * @property string|null $title_en
 * @property string|null $excerpt_ar
 * @property string|null $excerpt_en
 * @property string|null $body_ar
 * @property string|null $body_en
 * @property bool $published_ar
 * @property bool $published_en
 * @property string|null $cover
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'slug_ar', 'slug_en', 'title_ar', 'title_en', 'excerpt_ar', 'excerpt_en',
    'body_ar', 'body_en', 'published_ar', 'published_en', 'cover', 'published_at',
])]
final class Post extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_ar' => 'boolean',
            'published_en' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Posts live in this locale, newest first.
     *
     * A post counts as live only when it is flagged published for the locale,
     * has a slug to be reached at, and has a publication date that has arrived.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? App::getLocale();

        return $query
            ->where("published_{$locale}", true)
            ->whereNotNull("slug_{$locale}")
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    /**
     * Field for the active locale, e.g. title() -> title_ar.
     */
    public function field(string $name, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();

        return (string) ($this->{"{$name}_{$locale}"} ?? '');
    }

    public function title(?string $locale = null): string
    {
        return $this->field('title', $locale);
    }

    public function slug(?string $locale = null): string
    {
        return $this->field('slug', $locale);
    }

    public function body(?string $locale = null): string
    {
        return $this->field('body', $locale);
    }

    /**
     * The summary shown on cards and in meta tags.
     *
     * Falls back to the opening of the body so a post without a hand-written
     * excerpt still gets a useful meta description rather than an empty one.
     */
    public function excerpt(?string $locale = null, int $length = 160): string
    {
        $excerpt = trim($this->field('excerpt', $locale));

        if ($excerpt !== '') {
            return $excerpt;
        }

        // Strip Markdown syntax so the fallback reads as prose, not source.
        $body = $this->body($locale);
        $body = preg_replace('/```.*?```/s', ' ', $body) ?? $body;
        $body = preg_replace('/[#>*_`\[\]()!-]+/u', ' ', $body) ?? $body;

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $body) ?? ''), $length);
    }

    /**
     * Is this post readable in the given locale?
     */
    public function isPublishedIn(string $locale): bool
    {
        return (bool) $this->{"published_{$locale}"}
            && trim((string) $this->{"slug_{$locale}"}) !== ''
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Roughly how long the post takes to read, in minutes.
     */
    public function readingMinutes(?string $locale = null): int
    {
        $words = str_word_count(strip_tags($this->body($locale)));

        // Arabic does not word-count reliably with str_word_count, so fall
        // back to whitespace splitting when it returns nothing useful.
        if ($words === 0) {
            $words = count(preg_split('/\s+/u', trim($this->body($locale))) ?: []);
        }

        return max(1, (int) ceil($words / 200));
    }
}
