<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The dashboard, its login and Livewire's update requests use the site's
 * primary language (Settings) rather than whatever APP_LOCALE is in .env.
 * Public routes are left to SetLocale.
 */
final class DashboardLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*', 'livewire*')) {
            App::setLocale(Settings::primaryLocale());
        }

        return $next($request);
    }
}
