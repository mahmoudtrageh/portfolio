@props([
    // Per-page SEO. Every one falls back to the site defaults, so the homepage
    // and any page that sets nothing still get complete metadata.
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'canonical' => null,
    // hreflang. `true` swaps the locale segment in the current URL, which is
    // right for pages whose path is identical across languages. Pages whose
    // path differs per locale — a post with a translated slug — pass an
    // explicit ['ar' => url, 'en' => url] map instead. `false` emits none.
    'alternates' => true,
    // Raw JSON-LD for the page, as an array.
    'schema' => null,
    'feed' => false,
])

@php
    use App\Support\Content;
    use App\Support\Settings;

    $locale = app()->getLocale();
    $identity = Content::get('identity');

    $siteName = $identity['name'].' — '.$identity['role'];
    $pageTitle = $title ? $title.' · '.$identity['name'] : $siteName;
    $metaDescription = $description ?: Content::string('hero.summary');
    $metaImage = $image ?: asset('images/og-'.$locale.'.png');
    $canonicalUrl = $canonical ?: request()->url();

    $localePattern = '#/('.implode('|', config('portfolio.locales')).')(/|$)#';

    $pinnedDark = ! Settings::themeToggle() && Settings::theme() === 'dark';
@endphp

<!DOCTYPE html>
{{-- The class is emitted only when the theme is pinned to dark, so the
     tag is byte-identical to what it always was in every other case. --}}
<html lang="{{ $locale }}" dir="{{ Content::dir() }}"{!! $pinnedDark ? ' class="dark"' : '' !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="author" content="{{ $identity['name'] }}">

    {{-- Applied before first paint so the correct theme is painted once,
         never flashed.

         With the toggle switched off the theme is pinned for everyone: the
         stored preference is ignored rather than cleared, so a visitor who had
         chosen a theme gets it back if the toggle ever returns. --}}
    @if (Settings::themeToggle())
        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('theme');
                    var dark = stored
                        ? stored === 'dark'
                        : window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.classList.toggle('dark', dark);
                    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
                } catch (e) {}
            })();
        </script>
    @else
        <script>
            (function () {
                var dark = @json(Settings::theme() === 'dark');
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            })();
        </script>
    @endif

    {{-- hreflang pairs for bilingual SEO. Suppressed on pages that exist in a
         single locale, where the pair would point at a 404. --}}
    @php
        $hreflang = [];

        if (is_array($alternates)) {
            // Explicit per-locale URLs, for paths that differ by language.
            $hreflang = $alternates;
        } elseif ($alternates) {
            foreach (Settings::activeLocales() as $alt) {
                $hreflang[$alt] = preg_replace($localePattern, '/'.$alt.'$2', $canonicalUrl, 1);
            }
        }
    @endphp

    @foreach ($hreflang as $altLocale => $altUrl)
        <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}">
    @endforeach

    @if ($hreflang !== [])
        <link rel="alternate" hreflang="x-default"
              href="{{ $hreflang[config('app.locale')] ?? reset($hreflang) }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @if ($feed && Content::blogEnabled())
        <link rel="alternate" type="application/rss+xml"
              title="{{ $identity['name'] }} — {{ __('Blog') }}"
              href="{{ route('feed', ['locale' => $locale]) }}">
    @endif

    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ $identity['name'] }}">
    <meta property="og:locale" content="{{ $locale === 'ar' ? 'ar_EG' : 'en_US' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

    @if ($schema)
        {{-- JSON-LD: what search engines read to build a rich result. --}}
        <script type="application/ld+json">
            {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif

    <x-favicon />

    {{-- Emits the @font-face rules and preload hints for the families declared
         in vite.config.js. Without this the font files are built but never
         referenced, and every page silently falls back to a system face. --}}
    {{ Vite::fonts() }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh flex flex-col antialiased">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:inline-start-4 focus:z-50 focus:rounded-lg focus:px-4 focus:py-2"
       style="background: var(--accent-fill); color: var(--accent-text);">
        {{ __('Skip to content') }}
    </a>

    <x-navbar />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-footer />

    <x-lightbox />
</body>
</html>
