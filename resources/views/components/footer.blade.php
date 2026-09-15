@php
    use App\Support\Content;

    $identity = Content::get('identity');
    // Social links still holding a placeholder URL are hidden rather than
    // rendered as broken links.
    $socials = array_filter(
        Content::list('socials'),
        fn (array $s): bool => ! str_contains((string) $s['url'], '«')
    );

    // The rest of the Arabic page uses Arabic-Indic digits, so the copyright
    // year has to match rather than sitting there in Latin numerals.
    $year = date('Y');

    if (app()->getLocale() === 'ar') {
        $year = strtr($year, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
            '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
    }
@endphp

<footer class="border-t" style="border-color: var(--border);">
    <div class="shell py-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm">
            <p style="color: var(--text-muted);">
                © {{ $year }} {{ $identity['name'] }} · {{ __('All rights reserved.') }}
            </p>

            <ul class="flex flex-wrap gap-x-6 gap-y-2">
                <li>
                    <a href="mailto:{{ $identity['email'] }}"
                       class="transition-colors"
                       style="color: var(--text-muted);"
                       onmouseover="this.style.color='var(--text)'"
                       onmouseout="this.style.color='var(--text-muted)'">
                        {{ $identity['email'] }}
                    </a>
                </li>
                @foreach ($socials as $social)
                    <li>
                        <a href="{{ $social['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 transition-colors"
                           style="color: var(--text-muted);"
                           onmouseover="this.style.color='var(--text)'"
                           onmouseout="this.style.color='var(--text-muted)'">
                            <x-social-icon :name="$social['icon'] ?? ''" />
                            {{ $social['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>
