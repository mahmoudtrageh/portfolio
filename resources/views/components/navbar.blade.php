@php
    use App\Support\Content;
    use App\Support\Settings;

    $identity = Content::get('identity');
    $alt = Content::alternateLocale();

    $locale = app()->getLocale();
    $home = route('home', ['locale' => $locale]);

    // Each top-level page is its own route; `active` marks the current one.
    $links = [
        ['href' => $home, 'label' => __('Home'), 'active' => request()->routeIs('home')],
        ['href' => route('projects', ['locale' => $locale]), 'label' => __('Projects'), 'active' => request()->routeIs('projects', 'project')],
        ['href' => route('contact', ['locale' => $locale]), 'label' => __('Contact'), 'active' => request()->routeIs('contact')],
    ];

    if (Content::blogEnabled()) {
        $links[] = ['href' => route('blog', ['locale' => $locale]), 'label' => __('Blog'), 'active' => request()->routeIs('blog', 'post')];
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

            <a href="{{ $home }}" wire:navigate
               class="brand shrink-0"
               aria-label="{{ $identity['name'] }}">
                {{-- Two-tone wordmark: first name in ink, the rest in the accent. --}}
                @php [$brandFirst, $brandRest] = array_pad(explode(' ', trim((string) $identity['name']), 2), 2, ''); @endphp
                <span>{{ $brandFirst }}</span>@if ($brandRest !== '') <span class="brand__accent">{{ $brandRest }}</span>@endif
            </a>

            <div class="flex items-center gap-1.5 sm:gap-3">
            {{-- Desktop navigation, right-aligned beside the controls --}}
            <ul class="hidden md:flex items-center gap-1 text-sm">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['href'] }}" wire:navigate
                           class="nav-link {{ $link['active'] ? 'is-active' : '' }}"
                           @if ($link['active']) aria-current="page" @endif>
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
                        <path x-show="!open" d="M4 7h16M4 12h16M4 17h16"/>
                        <path x-show="open" x-cloak d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
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
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}" wire:navigate
                       x-on:click="open = false"
                       class="block py-3 border-b last:border-0"
                       style="border-color: var(--border); {{ $link['active'] ? 'font-weight: 600;' : 'color: var(--text-muted);' }}"
                       @if ($link['active']) aria-current="page" @endif>
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</header>
