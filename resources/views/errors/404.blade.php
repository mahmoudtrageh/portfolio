@php
    // A 404 means no route matched, so the locale middleware never ran and
    // nothing is bound. Read the locale straight off the URL instead, so
    // /en/nope still answers in English, and fall back to the app default.
    $segment = request()->segment(1);

    $locale = in_array($segment, config('portfolio.locales'), true)
        ? $segment
        : config('app.locale');

    app()->setLocale($locale);
    app('url')->defaults(['locale' => $locale]);
@endphp

<x-layout>
    <section class="section">
        <div class="shell text-center py-20">
            <p class="font-display text-6xl" style="color: var(--accent);">404</p>

            <h1 class="mt-6 font-display text-2xl sm:text-3xl">
                {{ __('Page not found') }}
            </h1>

            <p class="mt-3 text-base" style="color: var(--text-muted);">
                {{ __("The page you're looking for doesn't exist.") }}
            </p>

            <a href="{{ route('home') }}"
               class="mt-8 inline-flex px-6 py-3 rounded-full font-semibold"
               style="background: var(--accent-fill); color: var(--accent-text);">
                {{ __('Back home') }}
            </a>
        </div>
    </section>
</x-layout>
