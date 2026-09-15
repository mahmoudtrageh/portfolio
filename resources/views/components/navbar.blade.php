@php
    use App\Support\Content;
    use App\Support\Settings;

    $identity = Content::get('identity');
    $alt = Content::alternateLocale();

    // The dark logo falls back to the light one, so a single upload still
    // works in both themes.
    $logoLight = Content::image($identity['logo_image'] ?? '');
    $logoDark = Content::image($identity['logo_image_dark'] ?? '');

    // The site is one page plus the blog, so in-page anchors have to resolve
    // against the homepage — from /blog a bare "#work" would go nowhere.
    $home = route('home', ['locale' => app()->getLocale()]);
    $onHome = request()->routeIs('home');
    $anchor = fn (string $hash): string => $onHome ? $hash : $home.$hash;

    $links = [
        ['href' => $anchor('#work'), 'label' => __('Work')],
        ['href' => $anchor('#experience'), 'label' => __('Experience')],
        ['href' => $anchor('#skills'), 'label' => __('Skills')],
        ['href' => $anchor('#about'), 'label' => __('About')],
    ];

    if (Content::blogEnabled()) {
        $links[] = ['href' => route('blog', ['locale' => app()->getLocale()]), 'label' => __('Blog')];
    }

    // Same page, other language — keeps the visitor in place when switching.
    $altUrl = preg_replace(
        '#/('.implode('|', config('portfolio.locales')).')(/|$)#',
        '/'.$alt.'$2',
        request()->getRequestUri(),
        1
    );

    // A blog post's slug is translated, so swapping the locale segment would
    // land on a 404. Point at the post's real URL in the other language, or at
    // the blog index when it has no translation there.
    if (Content::blogEnabled() && request()->routeIs('post')) {
        $current = request()->route('slug');

        $translated = App\Models\Post::query()
            ->where('slug_'.app()->getLocale(), $current)
            ->first();

        $altUrl = $translated?->isPublishedIn($alt)
            ? route('post', ['locale' => $alt, 'slug' => $translated->slug($alt)])
            : route('blog', ['locale' => $alt]);
    }
@endphp

<header
    x-data="{ open: false, scrolled: false }"
    x-on:scroll.window="scrolled = window.scrollY > 8"
    class="sticky top-0 z-40 transition-colors duration-300"
    :class="scrolled || open ? 'backdrop-blur-md' : ''"
    style="background: color-mix(in oklab, var(--bg) 85%, transparent);"
>
    <div class="shell">
        <nav class="flex items-center justify-between gap-4 py-4 border-b"
             style="border-color: var(--border);"
             aria-label="{{ __('Main') }}">

            <a href="{{ $onHome ? '#top' : $home }}"
               class="font-display text-xl shrink-0 transition-opacity hover:opacity-70"
               aria-label="{{ $identity['name'] }}">
                {{-- The uploaded logo replaces the text one when present; the
                     text stays as the alt so the link is never empty.

                     When a dark-mode logo exists, both are rendered and CSS
                     picks one. The theme is toggled client-side by a class on
                     <html>, so choosing in PHP would serve a cached page the
                     wrong logo and would not update when the visitor switches. --}}
                @if ($logoLight !== '')
                    <img src="{{ $logoLight }}"
                         alt="{{ $identity['logo'] }}"
                         class="h-8 w-auto max-w-40 object-contain {{ $logoDark !== '' ? 'logo-light' : '' }}"
                         loading="eager" decoding="async">

                    @if ($logoDark !== '')
                        <img src="{{ $logoDark }}"
                             alt="{{ $identity['logo'] }}"
                             class="h-8 w-auto max-w-40 object-contain logo-dark"
                             loading="eager" decoding="async">
                    @endif
                @else
                    {{ $identity['logo'] }}
                @endif
            </a>

            {{-- Desktop navigation --}}
            <ul class="hidden md:flex items-center gap-1 text-sm">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['href'] }}"
                           class="px-3 py-2 rounded-lg block transition-colors"
                           style="color: var(--text-muted);"
                           onmouseover="this.style.color='var(--text)'"
                           onmouseout="this.style.color='var(--text-muted)'">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="flex items-center gap-1.5">
                @if (Settings::themeToggle())
                    <x-theme-toggle />
                @endif

                @if (Settings::multilingual())
                <a href="{{ $altUrl }}"
                   hreflang="{{ $alt }}"
                   lang="{{ $alt }}"
                   title="{{ __('Switch language') }}"
                   class="px-2.5 py-1.5 rounded-lg text-sm font-medium border transition-colors"
                   style="border-color: var(--border); color: var(--text-muted);"
                   onmouseover="this.style.color='var(--text)'; this.style.borderColor='var(--border-strong)'"
                   onmouseout="this.style.color='var(--text-muted)'; this.style.borderColor='var(--border)'">
                    {{ config('portfolio.locale_names')[$alt] }}
                </a>
                @endif

                <a href="{{ $anchor('#contact') }}"
                   class="hidden sm:inline-flex px-4 py-2 rounded-full text-sm font-semibold transition-colors"
                   style="background: var(--accent-fill); color: var(--accent-text);"
                   onmouseover="this.style.background='var(--accent-fill-hover)'"
                   onmouseout="this.style.background='var(--accent-fill)'">
                    {{ __("Let's talk") }}
                </a>

                {{-- Mobile menu trigger --}}
                <button type="button"
                        x-on:click="open = !open"
                        class="md:hidden p-2 rounded-lg border"
                        style="border-color: var(--border);"
                        :aria-expanded="open.toString()"
                        aria-controls="mobile-nav"
                        :aria-label="open ? '{{ __('Close menu') }}' : '{{ __('Open menu') }}'">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <template x-if="!open">
                            <g><path d="M4 7h16M4 12h16M4 17h16"/></g>
                        </template>
                        <template x-if="open">
                            <g><path d="M6 6l12 12M18 6L6 18"/></g>
                        </template>
                    </svg>
                </button>
            </div>
        </nav>
    </div>

    {{-- Mobile navigation --}}
    <div id="mobile-nav"
         x-show="open"
         x-collapse
         x-cloak
         class="md:hidden border-b"
         style="border-color: var(--border); background: var(--bg-elevated);">
        <ul class="shell py-3 flex flex-col">
            @foreach ([...$links, ['href' => $anchor('#contact'), 'label' => __('Contact')]] as $link)
                <li>
                    <a href="{{ $link['href'] }}"
                       x-on:click="open = false"
                       class="block py-3 border-b last:border-0"
                       style="border-color: var(--border);">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</header>
