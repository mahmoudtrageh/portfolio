<x-layout :title="__('Contact')">
    <section class="shell page">
        <header class="page__head fade-up">
            <h1 class="page__title">{{ __('Contact') }}</h1>
            <p class="page__lead">{{ __('Open to new projects and roles. The fastest way to reach me is email.') }}</p>
        </header>

        <dl class="max-w-2xl border-t fade-up" style="border-color: var(--border);">
            @foreach ($contacts as $row)
                <div class="py-4 sm:py-5 grid sm:grid-cols-[7rem_1fr] gap-1 sm:gap-4 border-b" style="border-color: var(--border);">
                    <dt class="text-sm sm:text-base" style="color: var(--text-faint);">{{ $row['label'] }}</dt>
                    <dd class="min-w-0 break-words">
                        <a href="{{ $row['href'] }}" class="intro-link" dir="ltr"
                           @if ($row['external']) target="_blank" rel="noopener noreferrer" @endif>{{ $row['value'] }}</a>
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>
</x-layout>
