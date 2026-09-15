@props(['job'])

@php
    // A rating is optional: entries carried over from elsewhere on the site
    // have no client review attached.
    $rating = (float) ($job['rating'] ?? 0);
    $review = trim((string) ($job['review'] ?? ''));
    $client = trim((string) ($job['client'] ?? ''));
    $platform = trim((string) ($job['platform'] ?? ''));
    $url = trim((string) ($job['url'] ?? ''));

    // The rest of the Arabic page uses Arabic-Indic numerals, so the rating has
    // to match rather than sitting there in Latin digits.
    $formatRating = function (float $value): string {
        $text = rtrim(rtrim(number_format($value, 1), '0'), '.');

        return app()->getLocale() === 'ar'
            ? strtr($text, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
                '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩', '.' => '٫'])
            : $text;
    };
@endphp

<li data-reveal>
    <article class="card p-5 h-full flex flex-col">
        <div class="flex items-start justify-between gap-3">
            <h3 class="font-display text-lg leading-snug">
                @if ($url !== '')
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                       class="hover:opacity-70 transition-opacity">
                        {{ $job['name'] }} <span aria-hidden="true" class="text-xs">↗</span>
                    </a>
                @else
                    {{ $job['name'] }}
                @endif
            </h3>

            @if ($rating > 0)
                {{-- The numeric value is the accessible answer; the stars are
                     decoration, so a screen reader hears "4.8 out of 5" once
                     rather than five separate star glyphs. --}}
                <span class="shrink-0 flex items-center gap-1"
                      title="{{ __(':rating out of 5', ['rating' => $formatRating($rating)]) }}">
                    <span class="flex" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                 fill="{{ $i <= round($rating) ? 'var(--shape-orange)' : 'var(--border)' }}">
                                <path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>
                            </svg>
                        @endfor
                    </span>
                    <span class="sr-only">{{ __(':rating out of 5', ['rating' => $formatRating($rating)]) }}</span>
                    <span class="text-xs font-semibold" style="color: var(--text-muted);" aria-hidden="true">
                        {{ $formatRating($rating) }}
                    </span>
                </span>
            @endif
        </div>

        <p class="mt-1.5 flex flex-wrap items-center gap-2 text-xs" style="color: var(--text-faint);">
            @if ($platform !== '')
                <span class="font-semibold" style="color: var(--accent);">{{ $platform }}</span>
                <span aria-hidden="true">·</span>
            @endif
            <span><x-metric :text="$job['period']" /></span>
        </p>

        <p class="mt-3 text-sm leading-relaxed" style="color: var(--text-muted);">
            {{ $job['summary'] }}
        </p>

        @if ($review !== '')
            @php
                // Client reviews were written in Arabic and are quoted as they
                // were left. On the English page they still need dir="rtl", or
                // the punctuation drifts to the wrong end of the line.
                $reviewDir = preg_match('/\p{Arabic}/u', $review) === 1 ? 'rtl' : 'ltr';
            @endphp

            <figure class="mt-4 ps-3 border-inline-start" style="border-color: var(--border-strong);">
                <blockquote class="text-sm leading-relaxed" dir="{{ $reviewDir }}"
                            style="color: var(--text);">
                    {{ $review }}
                </blockquote>

                @if ($client !== '')
                    <figcaption class="mt-1.5 text-xs" dir="{{ $reviewDir }}"
                                style="color: var(--text-faint);">
                        — {{ $client }}
                    </figcaption>
                @endif
            </figure>
        @endif

        @if (count($job['stack'] ?? []) > 0)
            <ul class="mt-auto pt-4 flex flex-wrap gap-1.5" aria-label="{{ __('Built with') }}">
                @foreach ($job['stack'] as $tech)
                    <li class="chip">{{ $tech }}</li>
                @endforeach
            </ul>
        @endif
    </article>
</li>
