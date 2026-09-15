<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Content;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates every route the blog owns — its index, its posts and its RSS feed.
 *
 * A disabled blog 404s rather than redirecting: to a crawler the section is
 * gone, not moved, and a 404 is what makes it drop out of an index. The posts
 * themselves are untouched, so switching back on restores them as they were.
 */
final class EnsureBlogEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Content::blogEnabled(), 404);

        return $next($request);
    }
}
