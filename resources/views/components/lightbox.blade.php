{{--
    Full-size image viewer.

    One instance per page, opened by dispatching a `lightbox` event with a src
    and caption. Kept out of the markup until opened so nine full-resolution
    certificates are never downloaded just to render the page.
--}}
<div x-data="{ open: false, src: '', caption: '' }"
     x-on:lightbox.window="src = $event.detail.src; caption = $event.detail.caption; open = true"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8"
     style="background: oklch(0.15 0.01 70 / 0.85);"
     x-transition.opacity
     role="dialog"
     aria-modal="true"
     x-bind:aria-label="caption">

    {{-- Clicking the backdrop closes; the figure below stops that bubbling. --}}
    <div class="absolute inset-0" x-on:click="open = false" aria-hidden="true"></div>

    <figure class="relative max-w-4xl w-full" x-on:click.stop>
        <img x-bind:src="src"
             x-bind:alt="caption"
             class="w-full max-h-[80vh] object-contain rounded-lg"
             style="background: var(--bg-elevated);">

        <figcaption class="mt-3 text-center text-sm" style="color: oklch(0.95 0.008 85);"
                    x-text="caption"></figcaption>

        {{-- Positioned with logical properties so it stays on the outer corner
             in both directions. --}}
        <button type="button"
                x-on:click="open = false"
                class="absolute w-9 h-9 rounded-full flex items-center justify-center border"
                style="top: -0.75rem; inset-inline-end: -0.75rem;
                       background: var(--bg-elevated); border-color: var(--border); color: var(--text);"
                aria-label="{{ __('Close') }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </figure>
</div>
