<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ContentSection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves the bilingual content tree down to the active locale, so views never
 * deal with ['ar' => ..., 'en' => ...] shapes.
 *
 * Content lives in the content_sections table and is edited through the admin
 * dashboard. The whole tree is small and read on every request, so it is cached
 * as one entry and flushed on write (see ContentSection writes in the dashboard).
 */
final class Content
{
    /**
     * Cache key for the assembled content tree.
     */
    public const CACHE_KEY = 'content.tree';

    /**
     * Sections holding exactly one entry, which read as that entry rather than
     * as a one-element list.
     *
     * @var list<string>
     */
    public const SINGLETONS = ['identity', 'hero', 'about', 'contact', 'channel', 'settings'];

    /**
     * The whole content tree, straight from storage and still bilingual.
     *
     * @return array<string, mixed>
     */
    public static function tree(): array
    {
        /** @var array<string, mixed> $tree */
        $tree = Cache::rememberForever(self::CACHE_KEY, function (): array {
            $tree = [];

            foreach (ContentSection::query()->orderBy('position')->get() as $row) {
                $tree[$row->section][] = $row->data;
            }

            foreach (self::SINGLETONS as $section) {
                // A singleton reads as the entry itself, so `hero.stats` works
                // the same way it did when this was a config array.
                $tree[$section] = $tree[$section][0] ?? [];
            }

            return $tree;
        });

        return $tree;
    }

    /**
     * Drop the cached tree. Called after any write from the dashboard.
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Fetch a content key, localised. Supports dot notation ('hero.stats').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::localise(data_get(self::tree(), $key, $default));
    }

    /**
     * Fetch a content key known to hold a list of entries.
     *
     * @return list<array<string, mixed>>
     */
    public static function list(string $key): array
    {
        /** @var list<array<string, mixed>> $items */
        $items = array_values((array) self::get($key, []));

        return $items;
    }

    /**
     * Fetch a content key known to hold a single string.
     */
    public static function string(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * Recursively replace every ['ar' => ..., 'en' => ...] pair with the value
     * for the current locale. Any other array is walked and preserved.
     */
    public static function localise(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (self::isTranslatable($value)) {
            return $value[App::getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? reset($value);
        }

        return array_map(self::localise(...), $value);
    }

    /**
     * A translatable node is an array keyed by exactly the supported locales.
     *
     * @param  array<array-key, mixed>  $value
     */
    private static function isTranslatable(array $value): bool
    {
        return $value !== [] && array_keys($value) === self::locales();
    }

    /**
     * Public URL for an uploaded image, as a root-relative path.
     *
     * Storage::url() builds an absolute URL from APP_URL, which breaks the
     * moment the site is reached on any other hostname — a local .test domain,
     * a staging host, or the production domain if APP_URL is stale. A
     * root-relative path is correct on all of them.
     */
    public static function image(?string $path): string
    {
        $path = trim((string) $path);

        return $path === '' ? '' : '/storage/'.ltrim($path, '/');
    }

    /**
     * Whether the blog is switched on.
     *
     * The switch itself lives in Settings; this stays as the name the views
     * already call.
     */
    public static function blogEnabled(): bool
    {
        return Settings::blogEnabled();
    }

    /**
     * Text direction for the active locale.
     */
    public static function dir(): string
    {
        $dir = config('portfolio.dir.'.App::getLocale(), 'ltr');

        return is_string($dir) ? $dir : 'ltr';
    }

    /**
     * The locale a language switcher should point at.
     */
    public static function alternateLocale(): string
    {
        foreach (self::locales() as $locale) {
            if ($locale !== App::getLocale()) {
                return $locale;
            }
        }

        return App::getLocale();
    }

    /**
     * The locales this site is published in.
     *
     * @return list<string>
     */
    public static function locales(): array
    {
        /** @var list<string> $locales */
        $locales = (array) config('portfolio.locales', []);

        return $locales;
    }
}
