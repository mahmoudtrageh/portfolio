@php
    use App\Support\Content;

    $identity = Content::get('identity');
    $locale = app()->getLocale();

    $url = route('post', ['locale' => $locale, 'slug' => $post->slug()]);
    $cover = $post->cover ? url(Content::image($post->cover)) : null;

    // Each locale has its own slug, so hreflang cannot be derived by swapping
    // the locale segment — that would advertise the English slug under /ar,
    // which 404s. Only locales the post is actually published in are listed.
    $alternate = [];

    foreach (Content::locales() as $l) {
        if ($post->isPublishedIn($l)) {
            $alternate[$l] = route('post', ['locale' => $l, 'slug' => $post->slug($l)]);
        }
    }

    // A post live in one language only needs no alternates at all.
    $alternate = count($alternate) > 1 ? $alternate : false;

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title(),
        'description' => $post->excerpt($locale),
        'inLanguage' => $locale,
        'datePublished' => $post->published_at->toIso8601String(),
        'dateModified' => $post->updated_at?->toIso8601String() ?? $post->published_at->toIso8601String(),
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
        'author' => [
            '@type' => 'Person',
            'name' => $identity['name'],
            'jobTitle' => $identity['role'],
            'url' => route('home', ['locale' => $locale]),
        ],
        'publisher' => [
            '@type' => 'Person',
            'name' => $identity['name'],
            'url' => route('home', ['locale' => $locale]),
        ],
    ];

    if ($cover) {
        $schema['image'] = $cover;
    }

    // A second graph node: breadcrumbs give the search result its trail.
    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $identity['name'],
                'item' => route('home', ['locale' => $locale]),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => __('Blog'),
                'item' => route('blog', ['locale' => $locale]),
            ],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title()],
        ],
    ];

    $arrow = Content::dir() === 'rtl' ? '←' : '→';
    $back = Content::dir() === 'rtl' ? '→' : '←';
@endphp

<x-layout
    :title="$post->title()"
    :description="$post->excerpt($locale)"
    :image="$cover"
    type="article"
    :canonical="$url"
    :alternates="$alternate"
    :schema="[$schema, $breadcrumbs]"
    feed>

    <article class="section">
        <div class="shell">
            <div class="max-w-2xl mx-auto">

                <a href="{{ route('blog', ['locale' => $locale]) }}"
                   class="inline-flex items-center gap-2 text-sm font-medium mb-8"
                   style="color: var(--accent);">
                    <span aria-hidden="true">{{ $back }}</span>
                    {{ __('All posts') }}
                </a>

                <header>
                    <h1 class="font-display text-3xl sm:text-4xl leading-tight text-balance">
                        {{ $post->title() }}
                    </h1>

                    <p class="mt-4 flex flex-wrap items-center gap-2 text-sm"
                       style="color: var(--text-faint);">
                        <time datetime="{{ $post->published_at->toDateString() }}">
                            {{ $post->published_at->isoFormat('LL') }}
                        </time>
                        <span aria-hidden="true">·</span>
                        <span>{{ __(':count min read', ['count' => $post->readingMinutes($locale)]) }}</span>
                    </p>
                </header>

                @if ($post->cover)
                    <img src="{{ Content::image($post->cover) }}"
                         alt=""
                         width="1200" height="675"
                         loading="eager" decoding="async"
                         class="w-full aspect-video object-cover rounded-xl border mt-8"
                         style="border-color: var(--border); background: var(--bg-sunken);">
                @endif

                {{-- Rendered from Markdown; raw HTML in the source is escaped
                     at render time, so this is safe to print unescaped. --}}
                <div class="prose mt-10">
                    {!! $html !!}
                </div>

                @if ($newer || $older)
                    <nav class="mt-14 pt-8 border-t grid sm:grid-cols-2 gap-4"
                         style="border-color: var(--border);"
                         aria-label="{{ __('More posts') }}">
                        @if ($older)
                            <a href="{{ route('post', ['locale' => $locale, 'slug' => $older->slug()]) }}"
                               class="card p-4">
                                <span class="block text-xs" style="color: var(--text-faint);">
                                    {{ __('Older post') }}
                                </span>
                                <span class="mt-1 block text-sm font-medium">{{ $older->title() }}</span>
                            </a>
                        @endif

                        @if ($newer)
                            <a href="{{ route('post', ['locale' => $locale, 'slug' => $newer->slug()]) }}"
                               class="card p-4 {{ $older ? '' : 'sm:col-start-2' }} sm:text-end">
                                <span class="block text-xs" style="color: var(--text-faint);">
                                    {{ __('Newer post') }}
                                </span>
                                <span class="mt-1 block text-sm font-medium">{{ $newer->title() }}</span>
                            </a>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </article>

</x-layout>
