<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Site-wide switches, stored as the `settings` singleton section.
 *
 * These are settings rather than content: they are not bilingual, and they
 * decide whether a feature exists at all rather than what it says. They ride
 * on the same content_sections table (and the same cache) so the dashboard
 * edits them with the machinery that already exists.
 *
 * Every reader defaults to the permissive value, so a site whose settings row
 * has not been written yet behaves exactly as it did before this existed.
 */
final class Settings
{
    /** The section these live in. */
    public const SECTION = 'settings';

    /**
     * Whether the blog is switched on.
     *
     * Off takes the section away rather than hiding it: the blog routes 404,
     * and the nav link, homepage teaser, RSS feed and sitemap entries that
     * point at them go with it. Posts themselves are untouched.
     */
    public static function blogEnabled(): bool
    {
        return self::bool('blog_enabled');
    }

    /**
     * Whether the site is published in more than one language.
     *
     * Off does not unregister the other locale's routes — that would 404 every
     * link already in the wild. The routes stay, and the disabled locale
     * redirects to the primary one, so old URLs keep resolving to real pages.
     */
    public static function multilingual(): bool
    {
        return self::bool('multilingual');
    }

    /**
     * The one locale the site serves when multilingual is off.
     */
    public static function primaryLocale(): string
    {
        $locale = self::string('primary_locale', (string) config('app.locale'));

        return in_array($locale, Content::locales(), true)
            ? $locale
            : (string) config('app.locale');
    }

    /**
     * The locales actually offered to a visitor — both, or just the primary.
     *
     * @return list<string>
     */
    public static function activeLocales(): array
    {
        return self::multilingual() ? Content::locales() : [self::primaryLocale()];
    }

    /**
     * Whether the visitor may switch between light and dark.
     *
     * Off pins the site to `theme()` and removes the toggle.
     */
    public static function themeToggle(): bool
    {
        return self::bool('theme_toggle');
    }

    /**
     * The theme to pin to when the toggle is off.
     *
     * 'system' is only meaningful while the toggle is on; pinned means a
     * decision has been made, so anything but 'dark' reads as light.
     */
    public static function theme(): string
    {
        return self::string('theme', 'light') === 'dark' ? 'dark' : 'light';
    }

    /**
     * A switch, defaulting to on when it has never been set.
     */
    private static function bool(string $key): bool
    {
        $value = Content::get(self::SECTION.'.'.$key);

        return $value === null ? true : (bool) $value;
    }

    /**
     * A stored string, falling back when unset or blank.
     */
    private static function string(string $key, string $default): string
    {
        $value = Content::get(self::SECTION.'.'.$key);

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    }
}
