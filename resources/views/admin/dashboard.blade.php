@php
    use App\Support\Content;

    $sections = config('dashboard.sections');
@endphp

<x-admin.layout>
    <h1 class="font-display text-2xl mb-2">{{ __('Dashboard') }}</h1>

    <p class="text-sm mb-8" style="color: var(--text-muted);">
        {{ __('Edit every part of the site, in Arabic and English.') }}
    </p>

    <div class="grid sm:grid-cols-2 gap-3">
        <a href="{{ route('admin.settings') }}"
           class="card p-5 block transition-colors">
            <span class="block font-display text-lg">{{ __('Settings') }}</span>

            <span class="block mt-1 text-sm" style="color: var(--text-muted);">
                {{ __('Turn parts of the site on and off.') }}
            </span>
        </a>

        @foreach ($sections as $key => $section)
            <a href="{{ route('admin.section', $key) }}"
               class="card p-5 block transition-colors">
                <span class="block font-display text-lg">
                    {{ Content::localise($section['label']) }}
                </span>

                <span class="block mt-1 text-sm" style="color: var(--text-muted);">
                    @if (($section['type'] ?? 'list') === 'single')
                        {{ __('Single entry') }}
                    @else
                        {{ trans_choice('{0}No entries|{1}1 entry|[2,*]:count entries', $counts[$key] ?? 0, ['count' => $counts[$key] ?? 0]) }}
                    @endif
                </span>
            </a>
        @endforeach
    </div>
</x-admin.layout>
