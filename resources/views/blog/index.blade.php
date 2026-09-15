@php
    use App\Support\Content;

    $identity = Content::get('identity');
    $locale = app()->getLocale();

    $heading = __('Blog');
    $lead = __('Notes on backend engineering, infrastructure and the systems I work on.');

    // Blog schema, so the index is understood as a collection of posts rather
    // than a generic page.
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => $heading.' — '.$identity['name'],
        'description' => $lead,
        'url' => route('blog', ['locale' => $locale]),
        'inLanguage' => $locale,
        'author' => [
            '@type' => 'Person',
            'name' => $identity['name'],
            'jobTitle' => $identity['role'],
            'url' => route('home', ['locale' => $locale]),
        ],
    ];
@endphp

<x-layout :title="$heading" :description="$lead" :schema="$schema" feed>

    <section class="section relative">
        <x-shapes :variant="2" />

        <div class="shell relative">
            <x-section-heading :eyebrow="__('Writing')" :title="$heading" :lead="$lead" />

            @if ($posts->isEmpty())
                <p class="card p-8 text-center text-sm" style="color: var(--text-muted);">
                    {{ __('No posts yet — check back soon.') }}
                </p>
            @else
                <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($posts as $post)
                        <li data-reveal>
                            <a href="{{ route('post', ['locale' => $locale, 'slug' => $post->slug()]) }}"
                               class="card overflow-hidden h-full flex flex-col transition-colors">
                                @if ($post->cover)
                                    <img src="{{ Content::image($post->cover) }}"
                                         alt=""
                                         width="800" height="450"
                                         loading="lazy" decoding="async"
                                         class="w-full aspect-video object-cover"
                                         style="background: var(--bg-sunken);">
                                @endif

                                <span class="p-5 flex flex-col flex-1">
                                    <span class="block font-display text-lg leading-snug">
                                        {{ $post->title() }}
                                    </span>

                                    <span class="mt-2 block text-sm leading-relaxed" style="color: var(--text-muted);">
                                        {{ $post->excerpt($locale, 120) }}
                                    </span>

                                    <span class="mt-auto pt-4 flex items-center gap-2 text-xs"
                                          style="color: var(--text-faint);">
                                        <time datetime="{{ $post->published_at->toDateString() }}">
                                            {{ $post->published_at->isoFormat('LL') }}
                                        </time>
                                        <span aria-hidden="true">·</span>
                                        <span>{{ __(':count min read', ['count' => $post->readingMinutes($locale)]) }}</span>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($posts->hasPages())
                    <div class="mt-10">
                        {{ $posts->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>

</x-layout>
