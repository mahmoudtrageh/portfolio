@props([
    'eyebrow' => null,
    'title',
    'lead' => null,
])

<div class="max-w-2xl mb-10">
    @if ($eyebrow)
        <p class="eyebrow mb-3">{{ $eyebrow }}</p>
    @endif

    <h2 class="font-display text-2xl sm:text-3xl text-balance">
        {{ $title }}
    </h2>

    @if ($lead)
        <p class="mt-3 text-base leading-relaxed" style="color: var(--text-muted);">
            {{ $lead }}
        </p>
    @endif
</div>
