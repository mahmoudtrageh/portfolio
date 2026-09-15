<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Resolve the locale from the {locale} route segment and configure the
     * application (and default URL generation) around it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');

        if (! in_array($locale, config('portfolio.locales'), true)) {
            $locale = (string) config('app.locale');
        }

        // With one language published, the other's URLs still resolve: they
        // redirect to the same path in the language that is live, so links
        // already shared keep working instead of 404ing. Permanent, because
        // the decision is an editorial one rather than a temporary outage.
        $primary = Settings::primaryLocale();

        if (! Settings::multilingual() && $locale !== $primary) {
            $to = preg_replace(
                '#^/'.preg_quote($locale, '#').'(/|$)#',
                '/'.$primary.'$1',
                $request->getRequestUri(),
                1
            );

            return redirect($to ?? '/'.$primary, 301);
        }

        App::setLocale($locale);

        // Every route in the site is locale-prefixed, so bind the parameter
        // globally to keep route() calls free of an explicit locale argument.
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
