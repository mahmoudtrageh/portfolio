@php
    use App\Http\Controllers\ProjectController;
    use App\Support\Content;

    $identity = Content::get('identity');
    $locale = app()->getLocale();

    $url = route('project', ['locale' => $locale, 'slug' => $project['slug']]);
    $cover = ! empty($project['cover']) ? url(Content::image($project['cover'])) : null;

    $liveUrl = trim((string) ($project['url'] ?? ''));
    $repoUrl = trim((string) ($project['repo'] ?? ''));

    // The facts panel only appears when at least one of its rows is filled.
    $facts = array_filter([
        __('My role') => trim((string) ($project['role'] ?? '')),
        __('Duration') => trim((string) ($project['timeline'] ?? '')),
        __('Team') => trim((string) ($project['team'] ?? '')),
        __('Status') => trim((string) ($project['status'] ?? '')),
    ], fn (string $v): bool => $v !== '');

    $headings = [
        'context' => __('Background & context'),
        'challenges' => __('Challenges'),
        'solution' => __('Solution & implementation'),
        'results' => __('Results & impact'),
        'lessons' => __('Lessons learned'),
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $project['name'],
        'headline' => $project['name'],
        'description' => $project['tagline'],
        'inLanguage' => $locale,
        'url' => $url,
        'author' => [
            '@type' => 'Person',
            'name' => $identity['name'],
            'jobTitle' => $identity['role'],
            'url' => route('home', ['locale' => $locale]),
        ],
    ];

    if ($cover) {
        $schema['image'] = $cover;
    }

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $identity['name'],
                'item' => route('home', ['locale' => $locale]),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => __('Projects'),
                'item' => route('home', ['locale' => $locale]).'#work',
            ],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $project['name']],
        ],
    ];

    $back = Content::dir() === 'rtl' ? '→' : '←';
@endphp

<x-layout
    :title="$project['name']"
    :description="$project['tagline']"
    :image="$cover"
    type="article"
    :canonical="$url"
    :schema="[$schema, $breadcrumbs]">

    <article class="section">
        <div class="shell">
            <div class="max-w-3xl mx-auto">

                <a href="{{ route('home', ['locale' => $locale]) }}#work"
                   class="inline-flex items-center gap-2 text-sm font-medium mb-8"
                   style="color: var(--accent);">
                    <span aria-hidden="true">{{ $back }}</span>
                    {{ __('All projects') }}
                </a>

                <header>
                    <p class="flex flex-wrap items-center gap-2 text-xs mb-3"
                       style="color: var(--text-faint);">
                        <span class="font-semibold" style="color: var(--accent);">
                            {{ $project['category'] }}
                        </span>
                        <span aria-hidden="true">·</span>
                        <span><x-metric :text="$project['year']" /></span>
                    </p>

                    <h1 class="font-display text-3xl sm:text-4xl leading-tight text-balance">
                        {{ $project['name'] }}
                    </h1>

                    <p class="mt-4 text-lg leading-relaxed" style="color: var(--text-muted);">
                        {{ $project['tagline'] }}
                    </p>

                    @if ($liveUrl !== '' || $repoUrl !== '')
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($liveUrl !== '')
                                <a href="{{ $liveUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold"
                                   style="background: var(--accent-fill); color: var(--accent-text);">
                                    {{ __('Visit project') }} <x-external-arrow />
                                </a>
                            @endif

                            @if ($repoUrl !== '')
                                <a href="{{ $repoUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold border"
                                   style="border-color: var(--border-strong); color: var(--text);">
                                    {{ __('Source code') }} <x-external-arrow />
                                </a>
                            @endif
                        </div>
                    @endif
                </header>

                @if (! empty($project['cover']))
                    <img src="{{ Content::image($project['cover']) }}"
                         alt=""
                         width="1200" height="675"
                         loading="eager" decoding="async"
                         class="w-full aspect-video object-cover rounded-xl border mt-8"
                         style="border-color: var(--border); background: var(--bg-sunken);">
                @endif

                {{-- At-a-glance facts, so a reader skimming gets the shape of
                     the work before committing to the prose. --}}
                @if ($facts !== [])
                    <dl class="mt-8 grid sm:grid-cols-2 gap-px rounded-xl overflow-hidden border"
                        style="border-color: var(--border); background: var(--border);">
                        @foreach ($facts as $label => $value)
                            <div class="p-4" style="background: var(--bg-elevated);">
                                <dt class="text-xs" style="color: var(--text-faint);">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-medium"><x-metric :text="$value" /></dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                {{-- The outcome, stated once and early. --}}
                @if (! empty($project['metric']))
                    <p class="mt-8 ps-4 leading-relaxed border-inline-start"
                       style="border-color: var(--accent); color: var(--text);">
                        <x-metric :text="$project['metric']" />
                    </p>
                @endif

                @foreach ($sections as $key => $html)
                    <section class="mt-12">
                        <h2 class="font-display text-2xl mb-4">{{ $headings[$key] }}</h2>

                        {{-- Rendered from Markdown; raw HTML in the source is
                             escaped at render time, so this is safe unescaped. --}}
                        <div class="prose">{!! $html !!}</div>
                    </section>
                @endforeach

                @if (count($project['stack'] ?? []) > 0)
                    <section class="mt-12">
                        <h2 class="font-display text-2xl mb-4">{{ __('Built with') }}</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach ($project['stack'] as $tech)
                                <li class="chip">{{ $tech }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($previous || $next)
                    <nav class="mt-14 pt-8 border-t grid sm:grid-cols-2 gap-4"
                         style="border-color: var(--border);"
                         aria-label="{{ __('More projects') }}">
                        @if ($previous)
                            <a href="{{ route('project', ['locale' => $locale, 'slug' => $previous['slug']]) }}"
                               class="card p-4">
                                <span class="block text-xs" style="color: var(--text-faint);">
                                    {{ __('Previous project') }}
                                </span>
                                <span class="mt-1 block text-sm font-medium">{{ $previous['name'] }}</span>
                            </a>
                        @endif

                        @if ($next)
                            <a href="{{ route('project', ['locale' => $locale, 'slug' => $next['slug']]) }}"
                               class="card p-4 {{ $previous ? '' : 'sm:col-start-2' }} sm:text-end">
                                <span class="block text-xs" style="color: var(--text-faint);">
                                    {{ __('Next project') }}
                                </span>
                                <span class="mt-1 block text-sm font-medium">{{ $next['name'] }}</span>
                            </a>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </article>

</x-layout>
