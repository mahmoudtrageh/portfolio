@php
    use App\Support\Content;

    $locales = Content::locales();
    $dirFor = fn (string $l): string => config('portfolio.dir.'.$l, 'ltr');

    $inputStyle = 'background: var(--bg); border-color: var(--border); color: var(--text);';
    $inputClass = 'w-full rounded-lg px-3 py-2 text-sm border outline-none';
@endphp

<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-2xl">
            {{ $postId ? __('Edit post') : __('New post') }}
        </h1>

        <div class="flex items-center gap-3">
            <span wire:loading wire:target="save" class="text-sm" style="color: var(--text-muted);">
                {{ __('Saving…') }}
            </span>

            <span x-data="{ shown: false }"
                  x-on:saved.window="shown = true; setTimeout(() => shown = false, 2500)"
                  x-show="shown" x-cloak
                  class="text-sm font-medium" style="color: var(--accent);">
                {{ __('Saved') }}
            </span>

            @if ($postId)
                <a href="{{ route('admin.posts') }}" class="text-sm" style="color: var(--text-muted);">
                    {{ __('All posts') }}
                </a>
            @endif

            <button type="button" wire:click="save"
                    class="px-5 py-2.5 rounded-full text-sm font-semibold"
                    style="background: var(--accent-fill); color: var(--accent-text);">
                {{ __('Save post') }}
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

    {{-- Shared settings: these belong to the post, not to one language. --}}
    <div class="card p-4 sm:p-5 mb-4 grid sm:grid-cols-2 gap-4">
        <div>
            <label for="published-at" class="block text-sm font-medium mb-2">
                {{ __('Publish date') }}
            </label>
            <input type="datetime-local" id="published-at"
                   wire:model="form.published_at"
                   class="{{ $inputClass }}" style="{{ $inputStyle }}">
            <p class="mt-1.5 text-xs" style="color: var(--text-faint);">
                {{ __('A future date keeps the post hidden until then.') }}
            </p>
        </div>

        <div>
            <p class="block text-sm font-medium mb-2">{{ __('Cover image') }}</p>

            <div class="flex items-start gap-3">
                @php
                    $preview = null;

                    if ($coverUpload && $errors->missing('coverUpload')) {
                        try {
                            $preview = $coverUpload->temporaryUrl();
                        } catch (Throwable) {
                            $preview = null;
                        }
                    } elseif (! empty($form['cover'])) {
                        $preview = Content::image($form['cover']);
                    }
                @endphp

                @if ($preview)
                    <img src="{{ $preview }}" alt=""
                         class="w-24 h-14 rounded-lg object-cover border shrink-0"
                         style="border-color: var(--border); background: var(--bg);">
                @endif

                <div class="flex-1 min-w-0">
                    <input type="file" wire:model="coverUpload"
                           accept="image/png,image/jpeg,image/webp"
                           class="block w-full text-sm" style="color: var(--text-muted);">

                    <div wire:loading wire:target="coverUpload" class="mt-1 text-xs"
                         style="color: var(--text-muted);">
                        {{ __('Uploading…') }}
                    </div>

                    @error('coverUpload')
                        <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                    @enderror

                    @if ($preview)
                        <button type="button" wire:click="removeCover"
                                class="mt-1.5 text-xs font-medium" style="color: oklch(0.6 0.2 25);">
                            {{ __('Remove image') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Language tabs. Each locale is written independently, so the editor
         shows one at a time rather than doubling every field on screen. --}}
    <div class="flex items-center gap-1 mb-4" role="tablist">
        @foreach ($locales as $locale)
            @php $active = $tab === $locale; @endphp
            <button type="button"
                    wire:click="$set('tab', '{{ $locale }}')"
                    role="tab"
                    aria-selected="{{ $active ? 'true' : 'false' }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    style="{{ $active
                        ? 'background: var(--accent-soft); color: var(--accent);'
                        : 'color: var(--text-muted);' }}">
                {{ config('portfolio.locale_names')[$locale] }}

                @if ($form['published_'.$locale] ?? false)
                    <span class="ms-1 inline-block w-1.5 h-1.5 rounded-full align-middle"
                          style="background: var(--shape-green);"
                          title="{{ __('Published') }}"></span>
                @endif
            </button>
        @endforeach
    </div>

    @foreach ($locales as $locale)
        <div class="{{ $tab === $locale ? '' : 'hidden' }}" wire:key="pane-{{ $locale }}">
            <div class="card p-4 sm:p-5 space-y-4">

                <label class="flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" wire:model.live="form.published_{{ $locale }}"
                           style="accent-color: var(--accent);">
                    {{ __('Publish in this language') }}
                </label>

                <div>
                    <label for="title-{{ $locale }}" class="block text-sm font-medium mb-2">
                        {{ __('Title') }}
                    </label>
                    <input type="text" id="title-{{ $locale }}"
                           wire:model.blur="form.title_{{ $locale }}"
                           dir="{{ $dirFor($locale) }}"
                           class="{{ $inputClass }}" style="{{ $inputStyle }}">
                    @error('form.title_'.$locale)
                        <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug-{{ $locale }}" class="block text-sm font-medium mb-2">
                        {{ __('Slug') }}
                    </label>
                    <input type="text" id="slug-{{ $locale }}"
                           wire:model="form.slug_{{ $locale }}"
                           dir="{{ $dirFor($locale) }}"
                           class="{{ $inputClass }}" style="{{ $inputStyle }}">
                    <p class="mt-1.5 text-xs" style="color: var(--text-faint);">
                        {{ __('The URL for this post. Filled in from the title, and safe to edit.') }}
                        <span dir="ltr">/{{ $locale }}/blog/{{ $form['slug_'.$locale] ?: '…' }}</span>
                    </p>
                    @error('form.slug_'.$locale)
                        <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="excerpt-{{ $locale }}" class="block text-sm font-medium mb-2">
                        {{ __('Excerpt') }}
                    </label>
                    <textarea id="excerpt-{{ $locale }}" rows="2"
                              wire:model="form.excerpt_{{ $locale }}"
                              dir="{{ $dirFor($locale) }}"
                              class="{{ $inputClass }} resize-y" style="{{ $inputStyle }}"></textarea>
                    <p class="mt-1.5 text-xs" style="color: var(--text-faint);">
                        {{ __('Shown on cards and used as the search-engine description. Taken from the opening of the post if left empty.') }}
                    </p>
                    @error('form.excerpt_'.$locale)
                        <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <label for="body-{{ $locale }}" class="block text-sm font-medium">
                            {{ __('Body') }}
                        </label>

                        <button type="button" wire:click="$toggle('preview')"
                                class="text-xs font-medium" style="color: var(--accent);">
                            {{ $preview ? __('Write') : __('Preview') }}
                        </button>
                    </div>

                    @if ($preview && $tab === $locale)
                        <div class="prose rounded-lg border p-4"
                             dir="{{ $dirFor($locale) }}"
                             style="border-color: var(--border); background: var(--bg);">
                            {!! $this->previewHtml() !!}
                        </div>
                    @else
                        <textarea id="body-{{ $locale }}" rows="18"
                                  wire:model="form.body_{{ $locale }}"
                                  dir="{{ $dirFor($locale) }}"
                                  class="{{ $inputClass }} resize-y font-mono text-[0.8125rem] leading-relaxed"
                                  style="{{ $inputStyle }}"></textarea>
                        <p class="mt-1.5 text-xs" style="color: var(--text-faint);">
                            {{ __('Markdown: ## heading, **bold**, [link](url), - list, ```code```') }}
                        </p>
                    @endif

                    @error('form.body_'.$locale)
                        <p class="mt-1 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    @endforeach

    @if ($postId)
        <div class="mt-6 pt-5 border-t flex items-center justify-between gap-4"
             style="border-color: var(--border);">
            <p class="text-xs" style="color: var(--text-faint);">
                {{ __('Deleting a post is permanent.') }}
            </p>

            <button type="button"
                    wire:click="delete"
                    wire:confirm="{{ __('Delete this post permanently?') }}"
                    class="text-sm font-medium"
                    style="color: oklch(0.6 0.2 25);">
                {{ __('Delete post') }}
            </button>
        </div>
    @endif
</div>
