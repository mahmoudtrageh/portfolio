@props(['items'])

{{-- Vertical rail uses logical border properties so it flips with direction. --}}
<ol class="relative border-inline-start ps-6 sm:ps-8 space-y-9"
    style="border-color: var(--border);">

    @foreach ($items as $item)
        <li class="relative" data-reveal>
            {{-- Node marker, offset onto the rail --}}
            <span class="absolute w-2.5 h-2.5 rounded-full ring-4"
                  style="inset-inline-start: calc(-1.5rem - 6px);
                         top: 0.55rem;
                         background: {{ $item['current'] ? 'var(--accent)' : 'var(--border-strong)' }};
                         --tw-ring-color: var(--bg);"
                  aria-hidden="true"></span>

            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                <h3 class="font-display text-lg">
                    {{ $item['role'] }}
                    @if ($item['current'])
                        <span class="align-middle ms-2 text-[0.7rem] font-semibold rounded-full px-2 py-0.5 font-sans"
                              style="background: var(--accent-soft); color: var(--accent);">
                            {{ __('Current') }}
                        </span>
                    @endif
                </h3>
                <p class="text-sm shrink-0" style="color: var(--text-faint);">
                    <x-metric :text="$item['period']" />
                </p>
            </div>

            <p class="mt-0.5 text-sm font-medium" style="color: var(--accent);">
                {{ $item['org'] }}
            </p>

            @if (! empty($item['product']))
                {{-- The shipped product, so a role points at something a reader
                     can actually go and look at. --}}
                <p class="mt-1.5 text-xs" style="color: var(--text-faint);">
                    @if (! empty($item['product_url']))
                        <a href="{{ $item['product_url'] }}" target="_blank" rel="noopener noreferrer"
                           class="underline underline-offset-2">
                            {{ $item['product'] }} <span aria-hidden="true">↗</span>
                        </a>
                    @else
                        {{ $item['product'] }}
                    @endif
                </p>
            @endif

            <ul class="mt-3 space-y-2">
                @foreach ($item['points'] as $point)
                    <li class="flex gap-2.5 text-sm leading-relaxed" style="color: var(--text-muted);">
                        <span class="shrink-0 mt-2 w-1 h-1 rounded-full"
                              style="background: var(--text-faint);" aria-hidden="true"></span>
                        <x-metric :text="$point" />
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
</ol>
