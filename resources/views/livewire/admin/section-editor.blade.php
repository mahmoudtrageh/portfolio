@php
    use App\Support\Content;
    use Illuminate\Support\Facades\Storage;

    $schema = $this->schema();
    $fields = $schema['fields'];
    $locales = Content::locales();
    $single = $this->isSingle();
    $titleField = $schema['title'] ?? null;

    // Inputs are bound per locale, so each one needs the direction of the
    // language it holds rather than the direction of the dashboard.
    $dirFor = fn (string $locale): string => config('portfolio.dir.'.$locale, 'ltr');

    $inputStyle = 'background: var(--bg); border-color: var(--border); color: var(--text);';
    $inputClass = 'w-full rounded-lg px-3 py-2 text-sm border outline-none';
@endphp

<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-2xl">
            {{ Content::localise($schema['label']) }}
        </h1>

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
             style="background: oklch(0.6 0.2 25 / 0.12); color: oklch(0.5 0.2 25);"
             role="alert">
            <p class="font-semibold mb-1">{{ __('Please fix the following:') }}</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_slice($errors->all(), 0, 8) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Entries --}}
    <div class="space-y-3">
        @forelse ($entries as $i => $entry)
            @php
                $label = $titleField
                    ? (Content::localise($entry[$titleField] ?? '') ?: __('Untitled'))
                    : __('Entry');
                $expanded = $open === $i;
            @endphp

            <div class="card overflow-hidden" wire:key="entry-{{ $i }}">

                {{-- Entry header: collapsed rows keep a long section scannable. --}}
                @unless ($single)
                    <div class="flex items-center gap-2 p-3">
                        <button type="button"
                                wire:click="$set('open', {{ $expanded ? 'null' : $i }})"
                                class="flex-1 flex items-center gap-2 text-start min-w-0"
                                aria-expanded="{{ $expanded ? 'true' : 'false' }}">
                            <svg class="w-4 h-4 shrink-0 transition-transform"
                                 style="{{ $expanded ? 'transform: rotate(90deg);' : '' }}
                                        color: var(--text-faint);"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="M9 6l6 6-6 6"/>
                            </svg>
                            <span class="text-sm font-medium truncate">{{ $label }}</span>
                        </button>

                        <div class="flex items-center gap-0.5 shrink-0">
                            <button type="button" wire:click="move({{ $i }}, -1)"
                                    @disabled($i === 0)
                                    class="p-1.5 rounded-md disabled:opacity-25"
                                    style="color: var(--text-faint);"
                                    aria-label="{{ __('Move up') }}">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true"><path d="M18 15l-6-6-6 6"/></svg>
                            </button>

                            <button type="button" wire:click="move({{ $i }}, 1)"
                                    @disabled($i === count($entries) - 1)
                                    class="p-1.5 rounded-md disabled:opacity-25"
                                    style="color: var(--text-faint);"
                                    aria-label="{{ __('Move down') }}">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                            </button>

                            <button type="button"
                                    wire:click="removeEntry({{ $i }})"
                                    wire:confirm="{{ __('Delete this entry?') }}"
                                    class="p-1.5 rounded-md"
                                    style="color: oklch(0.6 0.2 25);"
                                    aria-label="{{ __('Delete') }}">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true">
                                    <path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endunless

                {{-- Entry body --}}
                @if ($expanded || $single)
                    <div class="p-4 sm:p-5 space-y-5 {{ $single ? '' : 'border-t' }}"
                         style="border-color: var(--border);">

                        @foreach ($fields as $key => $field)
                            @php $fieldLabel = Content::localise($field['label']); @endphp

                            <div>
                                @if ($field['type'] === 'bool')
                                    <label class="flex items-center gap-2 text-sm font-medium">
                                        <input type="checkbox"
                                               wire:model="entries.{{ $i }}.{{ $key }}"
                                               style="accent-color: var(--accent);">
                                        {{ $fieldLabel }}
                                    </label>

                                @elseif ($field['type'] === 'file')
                                    @php
                                        $fileKey = $i.'.'.$key;
                                        $stored = (string) ($entry[$key] ?? '');
                                        $pendingFile = $uploads[$i][$key] ?? null;
                                    @endphp

                                    <p class="block text-sm font-medium mb-2">{{ $fieldLabel }}</p>

                                    <div class="rounded-lg border p-3"
                                         style="border-color: var(--border); background: var(--bg-sunken);">
                                        @if ($pendingFile)
                                            <p class="mb-2 text-xs" style="color: var(--text-muted);">
                                                {{ $pendingFile->getClientOriginalName() }}
                                                <span style="color: var(--text-faint);">
                                                    ({{ __('not saved yet') }})
                                                </span>
                                            </p>
                                        @elseif ($stored !== '')
                                            <a href="{{ Content::image($stored) }}" target="_blank"
                                               rel="noopener noreferrer"
                                               class="mb-2 inline-block text-xs underline underline-offset-2"
                                               style="color: var(--accent);">
                                                {{ __('View current file') }} <x-external-arrow size="w-3 h-3" />
                                            </a>
                                        @else
                                            <p class="mb-2 text-xs" style="color: var(--text-faint);">
                                                {{ __('None') }}
                                            </p>
                                        @endif

                                        <input type="file"
                                               wire:model="uploads.{{ $fileKey }}"
                                               accept="application/pdf"
                                               class="block w-full text-xs"
                                               style="color: var(--text-muted);">

                                        <div wire:loading wire:target="uploads.{{ $fileKey }}"
                                             class="mt-1 text-xs" style="color: var(--text-muted);">
                                            {{ __('Uploading…') }}
                                        </div>

                                        @error("uploads.{$fileKey}")
                                            <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                        @if (! empty($field['hint']))
                                            <p class="mt-2 text-xs leading-relaxed" style="color: var(--text-faint);">
                                                {{ Content::localise($field['hint']) }}
                                            </p>
                                        @endif

                                        @if ($pendingFile || $stored !== '')
                                            <button type="button"
                                                    wire:click="removeImage({{ $i }}, '{{ $key }}')"
                                                    class="mt-1.5 text-xs font-medium"
                                                    style="color: oklch(0.6 0.2 25);">
                                                {{ __('Remove') }}
                                            </button>
                                        @endif
                                    </div>

                                @elseif ($field['type'] === 'image')
                                    @php
                                        $uploadKey = $i.'.'.$key;
                                        $pending = $uploads[$i][$key] ?? null;
                                        $stored = (string) ($entry[$key] ?? '');

                                        // temporaryUrl() throws for anything not
                                        // previewable (a PDF, say), so a wrong
                                        // file must show the validation error
                                        // rather than crash the page.
                                        $preview = null;

                                        if ($pending && $errors->missing("uploads.{$uploadKey}")) {
                                            try {
                                                $preview = $pending->temporaryUrl();
                                            } catch (Throwable) {
                                                $preview = null;
                                            }
                                        } elseif ($stored !== '') {
                                            $preview = Content::image($stored);
                                        }
                                    @endphp

                                    <p class="block text-sm font-medium mb-2">{{ $fieldLabel }}</p>

                                    <div class="flex flex-wrap items-start gap-4">
                                        {{-- Preview: the pending upload if one is
                                             picked, otherwise what is stored. --}}
                                        @if ($preview)
                                            <img src="{{ $preview }}"
                                                 alt=""
                                                 class="w-20 h-20 rounded-lg object-contain border p-1 shrink-0"
                                                 style="border-color: var(--border); background: var(--bg);">
                                        @else
                                            <div class="w-20 h-20 rounded-lg border border-dashed shrink-0 flex items-center justify-center text-xs"
                                                 style="border-color: var(--border-strong); color: var(--text-faint);">
                                                {{ __('None') }}
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-48">
                                            <input type="file"
                                                   wire:model="uploads.{{ $uploadKey }}"
                                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                                   class="block w-full text-sm"
                                                   style="color: var(--text-muted);">

                                            <div wire:loading wire:target="uploads.{{ $uploadKey }}"
                                                 class="mt-1 text-xs" style="color: var(--text-muted);">
                                                {{ __('Uploading…') }}
                                            </div>

                                            @if (! empty($field['hint']))
                                                <p class="mt-1.5 text-xs leading-relaxed"
                                                   style="color: var(--text-faint);">
                                                    {{ Content::localise($field['hint']) }}
                                                </p>
                                            @endif

                                            @error("uploads.{$uploadKey}")
                                                <p class="mt-1.5 text-xs" style="color: oklch(0.6 0.2 25);">
                                                    {{ $message }}
                                                </p>
                                            @enderror

                                            @if ($pending || $stored !== '')
                                                <button type="button"
                                                        wire:click="removeImage({{ $i }}, '{{ $key }}')"
                                                        class="mt-2 text-xs font-medium"
                                                        style="color: oklch(0.6 0.2 25);">
                                                    {{ __('Remove image') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                @elseif ($field['type'] === 'plain')
                                    <label for="f-{{ $i }}-{{ $key }}"
                                           class="block text-sm font-medium mb-2">
                                        {{ $fieldLabel }}
                                    </label>
                                    <input type="text" id="f-{{ $i }}-{{ $key }}"
                                           wire:model="entries.{{ $i }}.{{ $key }}"
                                           dir="ltr"
                                           class="{{ $inputClass }}" style="{{ $inputStyle }}">

                                    @if (! empty($field['hint']))
                                        <p class="mt-1.5 text-xs leading-relaxed"
                                           style="color: var(--text-faint);">
                                            {{ Content::localise($field['hint']) }}
                                        </p>
                                    @endif

                                @elseif (in_array($field['type'], ['text', 'textarea', 'markdown'], true))
                                    {{-- Translated: both locales side by side, so a
                                         change is never made in one language alone. --}}
                                    <p class="block text-sm font-medium mb-2">{{ $fieldLabel }}</p>

                                    <div class="grid sm:grid-cols-2 gap-3">
                                        @foreach ($locales as $locale)
                                            <div>
                                                <label for="f-{{ $i }}-{{ $key }}-{{ $locale }}"
                                                       class="block text-xs mb-1"
                                                       style="color: var(--text-faint);">
                                                    {{ config('portfolio.locale_names')[$locale] }}
                                                </label>

                                                @if (in_array($field['type'], ['textarea', 'markdown'], true))
                                                    {{-- Markdown fields hold whole
                                                         case-study sections, so they
                                                         get more room and a monospace
                                                         face. --}}
                                                    <textarea id="f-{{ $i }}-{{ $key }}-{{ $locale }}"
                                                              wire:model="entries.{{ $i }}.{{ $key }}.{{ $locale }}"
                                                              rows="{{ $field['type'] === 'markdown' ? 10 : 3 }}"
                                                              dir="{{ $dirFor($locale) }}"
                                                              class="{{ $inputClass }} resize-y {{ $field['type'] === 'markdown' ? 'font-mono text-[0.8125rem] leading-relaxed' : '' }}"
                                                              style="{{ $inputStyle }}"></textarea>
                                                @else
                                                    <input type="text"
                                                           id="f-{{ $i }}-{{ $key }}-{{ $locale }}"
                                                           wire:model="entries.{{ $i }}.{{ $key }}.{{ $locale }}"
                                                           dir="{{ $dirFor($locale) }}"
                                                           class="{{ $inputClass }}"
                                                           style="{{ $inputStyle }}">
                                                @endif

                                                @error("entries.{$i}.{$key}.{$locale}")
                                                    <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>

                                    @if (! empty($field['hint']))
                                        <p class="mt-1.5 text-xs leading-relaxed" style="color: var(--text-faint);">
                                            {{ Content::localise($field['hint']) }}
                                        </p>
                                    @endif

                                    @if ($field['type'] === 'markdown')
                                        <p class="mt-1 text-xs" style="color: var(--text-faint);">
                                            {{ __('Markdown: ## heading, **bold**, [link](url), - list, ```code```') }}
                                        </p>
                                    @endif

                                @elseif ($field['type'] === 'list')
                                    {{-- Repeatable plain strings: a tech stack. --}}
                                    <p class="block text-sm font-medium mb-2">{{ $fieldLabel }}</p>

                                    <div class="space-y-2">
                                        @foreach ((array) ($entry[$key] ?? []) as $item => $value)
                                            <div class="flex gap-2" wire:key="li-{{ $i }}-{{ $key }}-{{ $item }}">
                                                <input type="text"
                                                       wire:model="entries.{{ $i }}.{{ $key }}.{{ $item }}"
                                                       dir="ltr"
                                                       class="{{ $inputClass }}" style="{{ $inputStyle }}">
                                                <button type="button"
                                                        wire:click="removeItem({{ $i }}, '{{ $key }}', {{ $item }})"
                                                        class="shrink-0 px-2 rounded-lg"
                                                        style="color: oklch(0.6 0.2 25);"
                                                        aria-label="{{ __('Remove') }}">×</button>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button"
                                            wire:click="addItem({{ $i }}, '{{ $key }}')"
                                            class="mt-2 text-sm font-medium"
                                            style="color: var(--accent);">
                                        + {{ __('Add') }}
                                    </button>

                                @elseif ($field['type'] === 'list.text')
                                    {{-- Repeatable translated strings: bullet points. --}}
                                    <p class="block text-sm font-medium mb-2">{{ $fieldLabel }}</p>

                                    <div class="space-y-3">
                                        @foreach ((array) ($entry[$key] ?? []) as $item => $value)
                                            <div class="rounded-lg border p-3"
                                                 style="border-color: var(--border); background: var(--bg-sunken);"
                                                 wire:key="lt-{{ $i }}-{{ $key }}-{{ $item }}">
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <span class="text-xs" style="color: var(--text-faint);">
                                                        {{ $item + 1 }}
                                                    </span>
                                                    <button type="button"
                                                            wire:click="removeItem({{ $i }}, '{{ $key }}', {{ $item }})"
                                                            class="text-sm"
                                                            style="color: oklch(0.6 0.2 25);"
                                                            aria-label="{{ __('Remove') }}">×</button>
                                                </div>

                                                <div class="grid sm:grid-cols-2 gap-3">
                                                    @foreach ($locales as $locale)
                                                        <div>
                                                            <span class="block text-xs mb-1"
                                                                  style="color: var(--text-faint);">
                                                                {{ config('portfolio.locale_names')[$locale] }}
                                                            </span>
                                                            <textarea wire:model="entries.{{ $i }}.{{ $key }}.{{ $item }}.{{ $locale }}"
                                                                      rows="2" dir="{{ $dirFor($locale) }}"
                                                                      class="{{ $inputClass }} resize-y"
                                                                      style="{{ $inputStyle }}"></textarea>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button"
                                            wire:click="addItem({{ $i }}, '{{ $key }}')"
                                            class="mt-2 text-sm font-medium"
                                            style="color: var(--accent);">
                                        + {{ __('Add') }}
                                    </button>

                                @elseif ($field['type'] === 'repeater')
                                    {{-- Repeatable group: hero stats, about sections. --}}
                                    <p class="block text-sm font-medium mb-2">{{ $fieldLabel }}</p>

                                    <div class="space-y-3">
                                        @foreach ((array) ($entry[$key] ?? []) as $item => $row)
                                            <div class="rounded-lg border p-3"
                                                 style="border-color: var(--border); background: var(--bg-sunken);"
                                                 wire:key="rp-{{ $i }}-{{ $key }}-{{ $item }}">
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <span class="text-xs" style="color: var(--text-faint);">
                                                        {{ $item + 1 }}
                                                    </span>
                                                    <button type="button"
                                                            wire:click="removeItem({{ $i }}, '{{ $key }}', {{ $item }})"
                                                            class="text-sm"
                                                            style="color: oklch(0.6 0.2 25);"
                                                            aria-label="{{ __('Remove') }}">×</button>
                                                </div>

                                                <div class="space-y-3">
                                                    @foreach ($field['fields'] as $subKey => $sub)
                                                        <div>
                                                            <span class="block text-xs font-medium mb-1">
                                                                {{ Content::localise($sub['label']) }}
                                                            </span>
                                                            @if ($sub['type'] === 'plain')
                                                                {{-- Not translated: an ID or URL is the
                                                                     same in every language. --}}
                                                                <input type="text"
                                                                       wire:model="entries.{{ $i }}.{{ $key }}.{{ $item }}.{{ $subKey }}"
                                                                       dir="ltr"
                                                                       class="{{ $inputClass }}"
                                                                       style="{{ $inputStyle }}">
                                                            @else
                                                                <div class="grid sm:grid-cols-2 gap-2">
                                                                    @foreach ($locales as $locale)
                                                                        <input type="text"
                                                                               wire:model="entries.{{ $i }}.{{ $key }}.{{ $item }}.{{ $subKey }}.{{ $locale }}"
                                                                               dir="{{ $dirFor($locale) }}"
                                                                               placeholder="{{ config('portfolio.locale_names')[$locale] }}"
                                                                               class="{{ $inputClass }}"
                                                                               style="{{ $inputStyle }}">
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button"
                                            wire:click="addItem({{ $i }}, '{{ $key }}')"
                                            class="mt-2 text-sm font-medium"
                                            style="color: var(--accent);">
                                        + {{ __('Add') }}
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="card p-6 text-sm text-center" style="color: var(--text-muted);">
                {{ __('Nothing here yet.') }}
            </p>
        @endforelse
    </div>

    @unless ($single)
        <button type="button"
                wire:click="addEntry"
                class="mt-4 w-full py-3 rounded-lg border border-dashed text-sm font-medium transition-colors"
                style="border-color: var(--border-strong); color: var(--text-muted);">
            + {{ __('Add entry') }}
        </button>
    @endunless
</div>
