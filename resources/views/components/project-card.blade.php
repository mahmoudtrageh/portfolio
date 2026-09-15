@props(['project'])

@php
    use App\Http\Controllers\ProjectController;

    // A project with written case-study sections gets a page of its own; the
    // card stays a teaser and links to it.
    $pageUrl = ProjectController::hasCaseStudy($project)
        ? route('project', ['locale' => app()->getLocale(), 'slug' => $project['slug']])
        : null;
@endphp

<article class="card p-6 flex flex-col h-full" data-reveal>
    <div class="flex items-center gap-2 flex-wrap text-xs" style="color: var(--text-faint);">
        <span class="font-semibold" style="color: var(--accent);">{{ $project['category'] }}</span>
        <span aria-hidden="true">·</span>
        <span>{{ $project['year'] }}</span>

        @if (! empty($project['status']))
            <span aria-hidden="true">·</span>
            <span>{{ $project['status'] }}</span>
        @endif
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

    <p class="mt-2 text-sm leading-relaxed" style="color: var(--text-muted);">
        {{ $project['tagline'] }}
    </p>

    {{-- The concrete outcome — every card carries one. --}}
    <p class="mt-4 ps-4 text-sm leading-relaxed border-inline-start"
       style="border-color: var(--accent); color: var(--text);">
        <x-metric :text="$project['metric']" />
    </p>

    @if ($pageUrl)
        <a href="{{ $pageUrl }}"
           class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold self-start"
           style="color: var(--accent);">
            {{ __('Read the case study') }}
            <span aria-hidden="true">{{ App\Support\Content::dir() === 'rtl' ? '←' : '→' }}</span>
        </a>
    @endif

    <ul class="mt-5 pt-4 border-t flex flex-wrap gap-1.5"
        style="border-color: var(--border);"
        aria-label="{{ __('Built with') }}">
        @foreach ($project['stack'] as $tech)
            <li class="chip">{{ $tech }}</li>
        @endforeach
    </ul>
</article>
