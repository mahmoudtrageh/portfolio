@props(['project'])

@php
    use App\Http\Controllers\ProjectController;

    use App\Support\Settings;

    // A project with written case-study sections gets a page of its own; the
    // card stays a teaser and links to it. Entries without a slug (the
    // "also worked on" list) never have a page, so they never link.
    //
    // The whole feature is behind a switch (Settings → Enable case-study
    // pages), so half-written studies stay unpublished.
    $slug = trim((string) ($project['slug'] ?? ''));

    $pageUrl = Settings::caseStudiesEnabled()
        && $slug !== ''
        && ProjectController::hasCaseStudy($project)
            ? route('project', ['locale' => app()->getLocale(), 'slug' => $slug])
            : null;

    // The live site, when there is one to visit.
    $visitUrl = trim((string) ($project['url'] ?? ''));
@endphp

<article class="card p-6 flex flex-col h-full" data-reveal>
    {{-- Smaller entries (the "also worked on" list) carry only some of these,
         so each part of the header is rendered only when it exists. --}}
    @php
        $meta = array_values(array_filter([
            trim((string) ($project['category'] ?? '')),
            trim((string) ($project['year'] ?? '')),
            trim((string) ($project['status'] ?? '')),
        ], fn (string $v): bool => $v !== ''));
    @endphp

    {{-- The row keeps its height even when empty, so a card with no year (or
         no category) still lines its title up with its neighbours. --}}
    <div class="flex items-center gap-2 flex-wrap text-xs min-h-[1.25rem]"
         style="color: var(--text-faint);"
         @if ($meta === []) aria-hidden="true" @endif>
        @foreach ($meta as $item)
            @if (! $loop->first)
                <span aria-hidden="true">·</span>
            @endif

            <span @class(['font-semibold' => $loop->first && ! empty($project['category'])])
                  @style(['color: var(--accent)' => $loop->first && ! empty($project['category'])])>
                {{ $item }}
            </span>
        @endforeach
    </div>

    <h3 class="mt-2.5 font-display text-xl">
        @if ($pageUrl)
            <a href="{{ $pageUrl }}" class="hover:opacity-70 transition-opacity">
                {{ $project['name'] }}
            </a>
        @else
            {{ $project['name'] }}
        @endif
    </h3>

    @php
        // Main projects describe themselves with a tagline; the smaller
        // entries carry a summary, or failing that the one-line note.
        $blurb = trim((string) ($project['tagline'] ?? $project['summary'] ?? $project['note'] ?? ''));
        $metric = trim((string) ($project['metric'] ?? ''));
    @endphp

    @if ($blurb !== '')
        <p class="mt-2 text-sm leading-relaxed" style="color: var(--text-muted);">
            {{ $blurb }}
        </p>
    @endif

    {{-- The concrete outcome. Entries without a measured result omit it
         rather than showing an empty rule. --}}
    @if ($metric !== '')
        <p class="mt-4 ps-4 text-sm leading-relaxed border-inline-start"
           style="border-color: var(--accent); color: var(--text);">
            <x-metric :text="$metric" />
        </p>
    @endif

    {{-- Everything below is the card's footer: it is pushed to the bottom as
         one block by mt-auto, so the stack rows of cards in a grid line up no
         matter how much text sits above them. --}}
    <div class="mt-auto">

    @if ($pageUrl || $visitUrl !== '')
        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2">
            @if ($pageUrl)
                <a href="{{ $pageUrl }}"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold"
                   style="color: var(--accent);">
                    {{ __('Read the case study') }}
                    <span aria-hidden="true">{{ App\Support\Content::dir() === 'rtl' ? '←' : '→' }}</span>
                </a>
            @endif

            {{-- rel="noopener" because the link opens in a new tab; without it
                 the opened page can reach back through window.opener.

                 x-external-arrow rather than "↗": that character renders as a
                 colour emoji on Windows and Android, which fights the accent. --}}
            @if ($visitUrl !== '')
                <a href="{{ $visitUrl }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold"
                   style="color: {{ $pageUrl ? 'var(--text-muted)' : 'var(--accent)' }};">
                    {{ __('Visit project') }}
                    <x-external-arrow />
                </a>
            @endif
        </div>
    @endif

    @if (count($project['stack'] ?? []) > 0)
        <ul class="mt-5 pt-4 border-t flex flex-wrap gap-1.5"
            style="border-color: var(--border);"
            aria-label="{{ __('Built with') }}">
            @foreach ($project['stack'] as $tech)
                <li class="chip">{{ $tech }}</li>
            @endforeach
        </ul>
    @endif

    </div>
</article>
