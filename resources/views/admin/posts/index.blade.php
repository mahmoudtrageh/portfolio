@php
    use App\Support\Content;

    $locales = Content::locales();
@endphp

<x-admin.layout :title="__('Blog posts')">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-2xl">{{ __('Blog posts') }}</h1>

        <a href="{{ route('admin.posts.create') }}"
           class="px-5 py-2.5 rounded-full text-sm font-semibold"
           style="background: var(--accent-fill); color: var(--accent-text);">
            + {{ __('New post') }}
        </a>
    </div>

    @if ($posts->isEmpty())
        <p class="card p-8 text-center text-sm" style="color: var(--text-muted);">
            {{ __('No posts yet. Write your first one.') }}
        </p>
    @else
        <ul class="space-y-2">
            @foreach ($posts as $post)
                <li class="card p-4">
                    <a href="{{ route('admin.posts.edit', $post) }}" class="flex items-start gap-3">
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-medium truncate">
                                {{ $post->title_ar ?: $post->title_en ?: __('Untitled') }}
                            </span>

                            <span class="mt-1 flex flex-wrap items-center gap-2 text-xs"
                                  style="color: var(--text-faint);">
                                @if ($post->published_at)
                                    <time datetime="{{ $post->published_at->toDateString() }}">
                                        {{ $post->published_at->isoFormat('LL') }}
                                    </time>
                                @else
                                    <span>{{ __('No date') }}</span>
                                @endif

                                {{-- Per-locale state: a post can be live in one
                                     language and still a draft in the other. --}}
                                @foreach ($locales as $locale)
                                    @php $live = $post->isPublishedIn($locale); @endphp
                                    <span class="px-1.5 py-0.5 rounded font-medium"
                                          style="{{ $live
                                              ? 'background: var(--accent-soft); color: var(--accent);'
                                              : 'background: var(--bg-sunken); color: var(--text-faint);' }}">
                                        {{ mb_strtoupper($locale) }}
                                        {{ $live ? __('live') : __('draft') }}
                                    </span>
                                @endforeach
                            </span>
                        </span>

                        <span class="shrink-0 text-xs" style="color: var(--text-faint);"
                              aria-hidden="true">›</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($posts->hasPages())
            <div class="mt-6">{{ $posts->links() }}</div>
        @endif
    @endif
</x-admin.layout>
