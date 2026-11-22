<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * With one language published, public URLs drop the locale prefix:
 * /projects rather than /en/projects.
 *
 * The routes themselves stay locale-prefixed, so switching multilingual back
 * on needs no other change. While it is off, this middleware (global, so it
 * runs before routing):
 *   - serves a bare public path as the primary locale's route,
 *   - 301-redirects any /en/… or /ar/… link to the bare path, so links already
 *     shared keep working and search engines move to the clean URL,
 *   - strips the prefix from every URL the site generates.
 */
final class UnprefixedLocale
{
    /**
     * First path segments of the public, locale-prefixed pages. Anything else
     * (/admin, /livewire, /storage, /sitemap.xml, /up, …) is left alone.
     *
     * @var list<string>
     */
    private const PUBLIC_ROOTS = ['', 'projects', 'contact', 'blog', 'feed.xml'];

    public function handle(Request $request, Closure $next): Response
    {
        if (Settings::multilingual()) {
            URL::formatPathUsing(fn (string $path): string => $path);

            return $next($request);
        }

        $primary = Settings::primaryLocale();
        $path = '/'.trim($request->getPathInfo(), '/');
        $segments = explode('/', ltrim($path, '/'), 2);

        // Old prefixed link: send it to the clean URL, query string kept.
        if (in_array($segments[0], config('portfolio.locales'), true)) {
            $query = $request->getQueryString();
            $to = '/'.($segments[1] ?? '').($query !== null ? '?'.$query : '');

            return redirect($to, $request->isMethod('GET') || $request->isMethod('HEAD') ? 301 : 308);
        }

        URL::formatPathUsing(fn (string $path): string => self::strip($path, $primary));

        if (! in_array($segments[0], self::PUBLIC_ROOTS, true)) {
            return $next($request);
        }

        // Route the bare path as its prefixed twin.
        $server = $request->server->all();
        $query = $request->getQueryString();
        $server['REQUEST_URI'] = $request->getBaseUrl().'/'.$primary.($path === '/' ? '' : $path).($query !== null ? '?'.$query : '');

        return $next($request->duplicate(server: $server));
    }

    /**
     * '/en/projects' → '/projects', '/en' → '/'. Other paths pass through.
     */
    private static function strip(string $path, string $locale): string
    {
        if ($path === '/'.$locale) {
            return '/';
        }

        return str_starts_with($path, '/'.$locale.'/') ? substr($path, strlen($locale) + 1) : $path;
    }
}
