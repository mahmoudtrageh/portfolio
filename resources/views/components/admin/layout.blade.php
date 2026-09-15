@props(['title' => null])

@php
    use App\Support\Content;

    $locale = app()->getLocale();
    $dir = config('portfolio.dir.'.$locale, 'ltr');
    $sections = config('dashboard.sections');
    $heading = $title ?? __('Dashboard');

    // Build the sidebar groups, keeping only sections that actually exist.
    $groups = [];
    $grouped = [];

    foreach (config('dashboard.groups') as $group) {
        $keys = array_values(array_filter(
            $group['sections'],
            fn (string $key): bool => isset($sections[$key]),
        ));

        if ($keys !== []) {
            $groups[] = ['label' => $group['label'], 'keys' => $keys];
            $grouped = [...$grouped, ...$keys];
        }
    }

    // A section added to the schema but not to a group would otherwise vanish
    // from the sidebar, so anything ungrouped is collected at the end.
    $ungrouped = array_values(array_diff(array_keys($sections), $grouped));

    if ($ungrouped !== []) {
        $groups[] = [
            'label' => ['ar' => 'أخرى', 'en' => 'Other'],
            'keys' => $ungrouped,
        ];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $heading }} · {{ __('Dashboard') }}</title>

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

    <x-favicon />

    {{-- See resources/views/components/layout.blade.php: without this the
         built font files are never referenced by the page. --}}
    {{ Vite::fonts() }}

    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-dvh antialiased" style="background: var(--bg-sunken);">

    <div class="flex min-h-dvh flex-col lg:flex-row">

        {{-- Sidebar --}}
        <aside x-data="{ open: false }"
               class="lg:w-64 lg:shrink-0 border-b lg:border-b-0 lg:border-e"
               style="border-color: var(--border); background: var(--bg-elevated);">

            <div class="flex items-center justify-between gap-3 p-4 lg:p-5">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-lg">
                    {{ __('Dashboard') }}
                </a>

                <button type="button"
                        x-on:click="open = !open"
                        class="lg:hidden p-2 rounded-lg border"
                        style="border-color: var(--border);"
                        :aria-expanded="open.toString()"
                        aria-controls="admin-nav"
                        aria-label="{{ __('Open menu') }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>

            {{-- Always visible from lg up; the button only toggles it below
                 that, so no JS runs before the desktop layout is correct. --}}
            <nav id="admin-nav"
                 class="px-3 pb-4 lg:block"
                 :class="open ? 'block' : 'hidden'"
                 aria-label="{{ __('Sections') }}">

                {{-- Blog sits above the content groups: it is a separate
                     resource with its own routes, not a content section.

                     Hidden while the blog is switched off, but still reachable
                     at its own URL — the editor keeps working, so drafts can be
                     written before the blog goes live and are never stranded
                     behind the switch. --}}
                @php
                    $onBlog = request()->routeIs('admin.posts*');
                    $onSettings = request()->routeIs('admin.settings');
                    $onMessages = request()->routeIs('admin.messages*');
                    $unreadMessages = App\Models\ContactMessage::query()->unread()->count();
                @endphp

                <div class="mb-4 space-y-0.5">
                    {{-- Messages sit at the top: they are the one thing here
                         that arrives on its own and may need answering. --}}
                    <a href="{{ route('admin.messages') }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm transition-colors"
                       style="{{ $onMessages
                           ? 'background: var(--accent-soft); color: var(--accent); font-weight: 600;'
                           : 'color: var(--text-muted);' }}"
                       @if ($onMessages) aria-current="page" @endif>
                        {{ __('Messages') }}

                        @if ($unreadMessages > 0)
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[0.7rem] font-semibold"
                                  style="background: var(--accent-fill); color: var(--accent-text);">
                                {{ $unreadMessages }}
                            </span>
                        @endif
                    </a>

                    @if (Content::blogEnabled() || $onBlog)
                        <a href="{{ route('admin.posts') }}"
                           class="block px-3 py-2 rounded-lg text-sm transition-colors"
                           style="{{ $onBlog
                               ? 'background: var(--accent-soft); color: var(--accent); font-weight: 600;'
                               : 'color: var(--text-muted);' }}"
                           @if ($onBlog) aria-current="page" @endif>
                            {{ __('Blog posts') }}
                        </a>
                    @endif

                    {{-- Settings is where the blog is switched back on, so it
                         is never hidden by any switch it controls. --}}
                    <a href="{{ route('admin.settings') }}"
                       class="block px-3 py-2 rounded-lg text-sm transition-colors"
                       style="{{ $onSettings
                           ? 'background: var(--accent-soft); color: var(--accent); font-weight: 600;'
                           : 'color: var(--text-muted);' }}"
                       @if ($onSettings) aria-current="page" @endif>
                        {{ __('Settings') }}
                    </a>
                </div>

                @foreach ($groups as $group)
                    <div class="mb-4">
                        <p class="px-3 mb-1 text-[0.6875rem] font-semibold uppercase tracking-[0.12em]"
                           style="color: var(--text-faint);">
                            {{ Content::localise($group['label']) }}
                        </p>

                        <ul class="space-y-0.5">
                            @foreach ($group['keys'] as $key)
                                @php $active = request()->route('section') === $key; @endphp
                                <li>
                                    <a href="{{ route('admin.section', $key) }}"
                                       class="block px-3 py-2 rounded-lg text-sm transition-colors"
                                       style="{{ $active
                                           ? 'background: var(--accent-soft); color: var(--accent); font-weight: 600;'
                                           : 'color: var(--text-muted);' }}"
                                       @if ($active) aria-current="page" @endif>
                                        {{ Content::localise($sections[$key]['label']) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div class="mt-4 pt-4 border-t px-3 space-y-2 text-sm"
                     style="border-color: var(--border);">
                    <a href="{{ route('home', ['locale' => $locale]) }}"
                       target="_blank" rel="noopener"
                       class="block transition-colors"
                       style="color: var(--text-muted);">
                        {{ __('View site') }} <x-external-arrow />
                    </a>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-start transition-colors"
                                style="color: var(--text-muted);">
                            {{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- Main --}}
        <main class="flex-1 min-w-0">
            <div class="mx-auto max-w-4xl p-5 sm:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Livewire 4 injects its own script tag; adding @livewireScripts here
         loads it twice and the second init leaves every wire: handler dead. --}}
</body>
</html>
