{{--
    Site-wide switches.

    Each switch is a card: the toggle itself, a line explaining what turning it
    off actually does, and — where the "off" state needs a choice — the field
    that answers it, revealed only when it applies.
--}}
<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display text-2xl">{{ __('Settings') }}</h1>
            <p class="text-sm mt-1" style="color: var(--text-muted);">
                {{ __('Turn parts of the site on and off.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span wire:loading wire:target="save" class="text-sm" style="color: var(--text-muted);">
                {{ __('Saving…') }}
            </span>

            <span x-data="{ shown: false }"
                  x-on:saved.window="shown = true; setTimeout(() => shown = false, 2500)"
                  x-show="shown"
                  x-cloak
                  class="text-sm font-medium"
                  style="color: var(--accent);">
                {{ __('Saved') }}
            </span>

            <button type="button"
                    wire:click="save"
                    class="px-5 py-2.5 rounded-full text-sm font-semibold transition-colors"
                    style="background: var(--accent-fill); color: var(--accent-text);">
                {{ __('Save changes') }}
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-lg p-4 mb-5 text-sm"
             style="background: oklch(0.6 0.2 25 / 0.12); color: oklch(0.5 0.2 25);">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-4">

        {{-- Blog --}}
        <div class="card p-5 sm:p-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox"
                       wire:model.live="blogEnabled"
                       class="mt-1"
                       style="accent-color: var(--accent);">
                <span>
                    <span class="block font-display text-lg">{{ __('Blog') }}</span>
                    <span class="block mt-1 text-sm" style="color: var(--text-muted);">
                        {{ __('Off removes the blog from the site: the navbar link, the latest-posts section on the homepage, the RSS feed, and the blog pages themselves. Your posts are kept, and come back exactly as they were when you switch it on again.') }}
                    </span>
                </span>
            </label>

            @unless ($blogEnabled)
                <p class="mt-3 ms-7 text-sm" style="color: var(--text-faint);">
                    {{ __('You can still write and edit posts while the blog is off.') }}
                </p>
            @endunless
        </div>

        {{-- Languages --}}
        <div class="card p-5 sm:p-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox"
                       wire:model.live="multilingual"
                       class="mt-1"
                       style="accent-color: var(--accent);">
                <span>
                    <span class="block font-display text-lg">{{ __('Both languages') }}</span>
                    <span class="block mt-1 text-sm" style="color: var(--text-muted);">
                        {{ __('Off publishes the site in one language and hides the language switcher. Addresses in the other language redirect to it rather than breaking, so links already shared keep working.') }}
                    </span>
                </span>
            </label>

            @unless ($multilingual)
                <div class="mt-4 ms-7">
                    <label class="block text-sm font-medium mb-2" for="primary-locale">
                        {{ __('Language to publish in') }}
                    </label>

                    <select id="primary-locale"
                            wire:model="primaryLocale"
                            class="rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--border); background: var(--bg); color: var(--text);">
                        @foreach ($locales as $locale)
                            <option value="{{ $locale }}">
                                {{ config('portfolio.locale_names')[$locale] ?? $locale }}
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-2 text-sm" style="color: var(--text-faint);">
                        {{ __('The content you have written in the other language is kept.') }}
                    </p>
                </div>
            @endunless
        </div>

        {{-- Theme --}}
        <div class="card p-5 sm:p-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox"
                       wire:model.live="themeToggle"
                       class="mt-1"
                       style="accent-color: var(--accent);">
                <span>
                    <span class="block font-display text-lg">{{ __('Light and dark') }}</span>
                    <span class="block mt-1 text-sm" style="color: var(--text-muted);">
                        {{ __('Off fixes the site to one theme for everyone and removes the toggle from the navbar.') }}
                    </span>
                </span>
            </label>

            @unless ($themeToggle)
                <div class="mt-4 ms-7">
                    <p class="block text-sm font-medium mb-2">{{ __('Theme to use') }}</p>

                    <div class="flex gap-4">
                        @foreach (['light' => __('Light'), 'dark' => __('Dark')] as $value => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio"
                                       wire:model="theme"
                                       value="{{ $value }}"
                                       style="accent-color: var(--accent);">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endunless
        </div>

    </div>
</div>
