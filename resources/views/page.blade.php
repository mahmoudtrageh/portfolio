@php
    use App\Support\Content;

    $identity = Content::get('identity');
    $photo = $identity['photo'] ?? '';
    $arrow = Content::dir() === 'rtl' ? '←' : '→';
    $socialLinks = array_filter($socials, fn (array $s): bool => ! str_contains((string) $s['url'], '«'));
    $talkHeading = __("Let's talk");

    // An empty channel name hides the block, so the section can be turned off
    // from the dashboard without touching the template. $videos comes from the
    // controller, already resolved to either the live feed or pinned entries.
    $channelName = trim((string) ($channel['name'] ?? ''));

    // An uploaded CV is a stored path; a legacy value may still be a URL, so
    // only bare paths are rewritten through the storage helper.
    $cvValue = trim((string) ($identity['cv'] ?? ''));
    $cv = str_starts_with($cvValue, 'uploads/') ? Content::image($cvValue) : $cvValue;
@endphp

<x-layout>

    {{-- ─────────────────────────── Hero ─────────────────────────── --}}
    <section id="top" class="section relative pt-14 sm:pt-20">
        <x-shapes :variant="1" />

        <div class="shell relative">
            {{-- Two columns once a header image is set; without one the text
                 keeps the full measure rather than leaving a gap.

                 The column widths live in a `hero-split` class rather than an
                 arbitrary Tailwind value: a class that only ever appears inside
                 a PHP conditional is invisible to Tailwind's scanner, so
                 `lg:grid-cols-[1.5fr_1fr]` was emitted into the HTML but never
                 generated as CSS. --}}
            <div class="{{ $photo ? 'hero-split' : '' }}">
            <div class="{{ $photo ? '' : 'max-w-3xl' }} fade-up">
                {{-- Availability pill. The status half carries a live dot and
                     the site's text colour; the location half is quieter and
                     sits behind a divider, so the eye reads "available" first
                     rather than one flat run of grey text. --}}
                <p class="status-pill mb-7">
                    <span class="status-pill__state">
                        <span class="status-dot" aria-hidden="true"></span>
                        {{ $identity['available'] }}
                    </span>

                    <span class="status-pill__where">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ $identity['location'] }}
                    </span>
                </p>

                <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.4rem] leading-[1.15] text-balance">
                    {{ $identity['name'] }}
                </h1>

                <p class="mt-4 text-lg sm:text-xl" style="color: var(--accent);">
                    {{ $identity['role'] }}
                </p>

                <p class="mt-6 text-base sm:text-lg leading-relaxed max-w-2xl" style="color: var(--text-muted);">
                    {{ $hero['eyebrow'] }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#contact"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-full font-semibold transition-colors"
                       style="background: var(--accent-fill); color: var(--accent-text);"
                       onmouseover="this.style.background='var(--accent-fill-hover)'"
                       onmouseout="this.style.background='var(--accent-fill)'">
                        {{ __('Get in touch') }}
                        <span aria-hidden="true">{{ $arrow }}</span>
                    </a>

                    {{-- Hidden until a CV is uploaded for this language: the
                         button used to point at a file that was never there,
                         so it 404'd. --}}
                    @if ($cv !== '')
                        <a href="{{ $cv }}"
                           download
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-full font-semibold border transition-colors"
                           style="border-color: var(--border-strong); color: var(--text);"
                           onmouseover="this.style.background='var(--bg-elevated)'"
                           onmouseout="this.style.background='transparent'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                            </svg>
                            {{ __('Download CV') }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($photo)
                {{-- Sits above the text on small screens, beside it from lg. --}}
                <div class="order-first lg:order-last fade-up">
                    <div class="relative mx-auto w-44 sm:w-56 lg:w-full lg:max-w-xs aspect-square">
                        <div class="absolute inset-0 rounded-2xl rotate-3"
                             style="background: var(--accent-soft);" aria-hidden="true"></div>

                        <img src="{{ Content::image($photo) }}"
                             alt="{{ $identity['name'] }} — {{ $identity['role'] }}"
                             width="480" height="480" loading="eager" decoding="async"
                             class="relative w-full h-full object-cover rounded-2xl border"
                             style="border-color: var(--border); background: var(--bg-sunken);">
                    </div>
                </div>
            @endif
            </div>

            {{-- Proof numbers. The counts are derived from the site's own
                 content, so they stay true as work is added. --}}
            <dl class="mt-14 grid grid-cols-3 gap-px rounded-xl overflow-hidden border"
                style="border-color: var(--border); background: var(--border);">
                @foreach ($stats as $stat)
                    <div class="dotted p-5" style="background: var(--bg-elevated);">
                        <dt class="font-display text-2xl sm:text-3xl" style="color: var(--accent);">
                            <x-metric :text="$stat['value']" />
                        </dt>
                        <dd class="mt-1.5 text-sm leading-snug" style="color: var(--text-muted);">
                            {{ $stat['label'] }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- ────────────────────────── Projects ───────────────────────── --}}
    <section id="work" class="section relative border-t" style="border-color: var(--border);">
        <x-shapes :variant="2" />

        <div class="shell relative">
            <x-section-heading
                :eyebrow="__('Selected work')"
                :title="__('Projects')"
                :lead="__('Systems I built, shipped and helped run.')" />

            <div class="grid md:grid-cols-2 gap-5">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>

            @if (count($otherProjects) > 0)
                <p class="mt-8 text-sm leading-relaxed" style="color: var(--text-muted);">
                    <span class="font-semibold" style="color: var(--text);">{{ __('Also worked on') }}:</span>
                    @foreach ($otherProjects as $other)
                        {{ $other['name'] }} <span style="color: var(--text-faint);">({{ $other['note'] }})</span>{{ $loop->last ? '' : ' · ' }}
                    @endforeach
                </p>
            @endif

            @if (count($sideProjects) > 0)
                {{-- Own work, kept distinct from client and employed work: these
                     are things built without a brief. --}}
                <div class="mt-12 pt-10 border-t" style="border-color: var(--border);">
                    <p class="eyebrow mb-5">{{ __('Side projects') }}</p>

                    <ul class="grid md:grid-cols-2 gap-5">
                        @foreach ($sideProjects as $side)
                            @php $sideUrl = trim((string) ($side['url'] ?? '')); @endphp

                            <li data-reveal>
                                <article class="card p-5 h-full flex flex-col">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-display text-lg leading-snug">
                                            @if ($sideUrl !== '')
                                                <a href="{{ $sideUrl }}" target="_blank" rel="noopener noreferrer"
                                                   class="hover:opacity-70 transition-opacity">
                                                    {{ $side['name'] }}
                                                    <span aria-hidden="true" class="text-xs">↗</span>
                                                </a>
                                            @else
                                                {{ $side['name'] }}
                                            @endif
                                        </h3>

                                        @if (! empty($side['status']))
                                            <span class="shrink-0 text-[0.7rem] font-semibold rounded-full px-2 py-0.5"
                                                  style="background: var(--accent-soft); color: var(--accent);">
                                                {{ $side['status'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if (! empty($side['tagline']))
                                        <p class="mt-1 text-sm font-medium" style="color: var(--text-muted);">
                                            {{ $side['tagline'] }}
                                        </p>
                                    @endif

                                    <p class="mt-3 text-sm leading-relaxed" style="color: var(--text-muted);">
                                        {{ $side['summary'] }}
                                    </p>

                                    @if (count($side['stack'] ?? []) > 0)
                                        <ul class="mt-auto pt-4 flex flex-wrap gap-1.5"
                                            aria-label="{{ __('Built with') }}">
                                            @foreach ($side['stack'] as $tech)
                                                <li class="chip">{{ $tech }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </article>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- ───────────────────────── Experience ──────────────────────── --}}
    <section id="experience" class="section border-t"
             style="border-color: var(--border); background: var(--bg-sunken);">
        <div class="shell">
            <x-section-heading :eyebrow="__('Career')" :title="__('Experience')" />

            @if (count($freelance) > 0)
                {{-- Two kinds of work, told separately: employment reads as a
                     timeline, independent work as a set of delivered projects.
                     Both panes stay in the DOM so the content is indexed and
                     reachable without JavaScript. --}}
                <div x-data="{ tab: 'jobs' }">
                    <div class="inline-flex items-center gap-1 p-1 mb-8 rounded-full border"
                         style="border-color: var(--border); background: var(--bg-elevated);"
                         role="tablist">
                        @foreach ([['jobs', __('Employment')], ['freelance', __('Freelancing')]] as [$key, $label])
                            <button type="button"
                                    role="tab"
                                    x-on:click="tab = '{{ $key }}'"
                                    x-bind:aria-selected="tab === '{{ $key }}' ? 'true' : 'false'"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors"
                                    x-bind:style="tab === '{{ $key }}'
                                        ? 'background: var(--accent-soft); color: var(--accent);'
                                        : 'color: var(--text-muted);'">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div x-show="tab === 'jobs'" role="tabpanel">
                        <x-timeline :items="$timeline" />
                    </div>

                    {{-- Hidden with x-cloak rather than a class, so it is in the
                         markup for crawlers but never flashes before Alpine
                         initialises. --}}
                    <div x-show="tab === 'freelance'" x-cloak role="tabpanel">
                        <ul class="grid md:grid-cols-2 gap-5">
                            @foreach ($freelance as $job)
                                <x-freelance-card :job="$job" />
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <x-timeline :items="$timeline" />
            @endif
        </div>
    </section>

    {{-- ─────────────────────────── Skills ────────────────────────── --}}
    <section id="skills" class="section relative border-t" style="border-color: var(--border);">
        <x-shapes :variant="3" />

        <div class="shell relative">
            <x-section-heading :eyebrow="__('The toolbox')" :title="__('Skills')" />

            <div class="grid sm:grid-cols-2 gap-x-10 gap-y-7">
                @foreach ($skills as $group)
                    <div data-reveal>
                        <h3 class="text-xs font-semibold uppercase tracking-[0.14em] mb-3"
                            style="color: var(--text-faint);">
                            {{ $group['group'] }}
                        </h3>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach ($group['items'] as $item)
                                <li class="chip">{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 pt-6 border-t text-sm"
               style="border-color: var(--border); color: var(--text-muted);">
                <span class="font-semibold" style="color: var(--text);">{{ __('Languages spoken') }}:</span>
                {{ $identity['spoken'] }}
            </p>
        </div>
    </section>

    {{-- ──────────────── About · Education · Certificates ─────────────── --}}
    <section id="about" class="section border-t"
             style="border-color: var(--border); background: var(--bg-sunken);">
        <div class="shell">
            <div class="grid lg:grid-cols-[1.1fr_1fr] gap-12 lg:gap-16">

                <div>
                    <x-section-heading :eyebrow="__('Background')" :title="__('About')" />

                    <p class="text-base leading-relaxed" style="color: var(--text-muted);">
                        {{ $about['intro'] }}
                    </p>

                    @foreach ($about['sections'] as $item)
                        <div class="mt-7" data-reveal>
                            <h3 class="font-display text-lg">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed" style="color: var(--text-muted);">
                                {{ $item['body'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Offset so the cards start level with the About prose, not
                     its heading. --}}
                <div class="lg:pt-[4.5rem]">
                    @foreach ($education as $item)
                        <div class="card dotted p-6" data-reveal>
                            <p class="eyebrow mb-3">{{ __('Education') }}</p>

                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                                <h3 class="font-display text-lg">
                                    <x-metric :text="$item['degree']" />
                                </h3>
                                <p class="text-sm shrink-0" style="color: var(--text-faint);">
                                    <x-metric :text="$item['period']" />
                                </p>
                            </div>

                            <p class="mt-1 text-sm font-medium" style="color: var(--accent);">
                                <x-metric :text="$item['school']" />
                            </p>
                            <p class="mt-3 text-sm leading-relaxed" style="color: var(--text-muted);">
                                {{ $item['note'] }}
                            </p>
                        </div>
                    @endforeach

                    {{-- x-data marks this subtree as an Alpine component. The
                         thumbnail buttons below use x-on:click, and Alpine only
                         binds directives inside a component scope — without it
                         the clicks are silently inert. --}}
                    <div class="card dotted mt-5 p-6" data-reveal x-data>
                        <p class="eyebrow mb-4">{{ __('Certificates') }}</p>

                        <ul class="space-y-3">
                            @foreach ($certificates as $certificate)
                                @php $thumb = Content::image($certificate['image'] ?? ''); @endphp

                                <li class="flex items-start gap-3 text-sm">
                                    @if ($thumb !== '')
                                        {{-- Opens the full certificate. A button
                                             rather than a link: there is no page
                                             to navigate to, and it must be
                                             reachable from the keyboard. --}}
                                        {{-- Kept on one line: Alpine parses the
                                             attribute as a JS expression, and a
                                             newline inside the object literal
                                             breaks it silently. --}}
                                        <button type="button"
                                                x-on:click="$dispatch('lightbox', { src: @js($thumb), caption: @js($certificate['name'].' — '.$certificate['issuer']) })"
                                                class="shrink-0 rounded border overflow-hidden transition-colors"
                                                style="border-color: var(--border);"
                                                onmouseover="this.style.borderColor='var(--accent)'"
                                                onmouseout="this.style.borderColor='var(--border)'"
                                                aria-label="{{ __('View certificate') }}: {{ $certificate['name'] }}">
                                            <img src="{{ $thumb }}"
                                                 alt=""
                                                 loading="lazy" decoding="async"
                                                 class="w-14 h-11 object-cover block"
                                                 style="background: var(--bg-sunken);">
                                        </button>
                                    @endif

                                    <span class="flex-1 min-w-0 flex items-baseline justify-between gap-3">
                                        <span class="min-w-0">
                                            <span class="block font-medium">{{ $certificate['name'] }}</span>
                                            <span class="block mt-0.5 text-xs" style="color: var(--text-faint);">
                                                {{ $certificate['issuer'] }}

                                                @if (! empty($certificate['url']))
                                                    <a href="{{ $certificate['url'] }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="ms-1 underline underline-offset-2"
                                                       style="color: var(--accent);">{{ __('Verify') }}</a>
                                                @endif
                                            </span>
                                        </span>

                                        <span class="text-xs shrink-0" style="color: var(--text-faint);">
                                            {{ $certificate['date'] }}
                                        </span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─────────────────────────── Blog ──────────────────────────── --}}
    @if (Content::blogEnabled() && $posts->isNotEmpty())
        <section id="blog" class="section border-t"
                 style="border-color: var(--border); background: var(--bg-sunken);">
            <div class="shell">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
                    <div class="max-w-2xl">
                        <p class="eyebrow mb-3">{{ __('Latest') }}</p>
                        <h2 class="font-display text-2xl sm:text-3xl text-balance">{{ __('From the blog') }}</h2>
                    </div>

                    <a href="{{ route('blog', ['locale' => app()->getLocale()]) }}"
                       class="text-sm font-semibold shrink-0"
                       style="color: var(--accent);">
                        {{ __('All posts') }} <span aria-hidden="true">{{ $arrow }}</span>
                    </a>
                </div>

                <ul class="grid sm:grid-cols-3 gap-5">
                    @foreach ($posts as $post)
                        <li data-reveal>
                            <a href="{{ route('post', ['locale' => app()->getLocale(), 'slug' => $post->slug()]) }}"
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

                                    <span class="mt-2 block text-sm leading-relaxed"
                                          style="color: var(--text-muted);">
                                        {{ $post->excerpt(null, 110) }}
                                    </span>

                                    <span class="mt-auto pt-4 text-xs" style="color: var(--text-faint);">
                                        <time datetime="{{ $post->published_at->toDateString() }}">
                                            {{ $post->published_at->isoFormat('LL') }}
                                        </time>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ────────────────────────── Writing ────────────────────────── --}}
    @if (count($writing) > 0 || $channelName !== '')
        <section id="writing" class="section relative border-t" style="border-color: var(--border);">
            <x-shapes :variant="2" />

            <div class="shell relative">
                <x-section-heading
                    :eyebrow="__('Also')"
                    :title="__('Writing & video')"
                    :lead="__('Volunteer articles published on Arabic tech and science outlets.')" />

                @if ($channelName !== '')
                    <a href="{{ $channel['url'] }}"
                       target="_blank" rel="noopener noreferrer"
                       class="card p-5 mb-4 flex items-center gap-4 transition-colors"
                       data-reveal>
                        <span class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center"
                              style="background: var(--accent-soft);" aria-hidden="true">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"
                                 style="color: var(--accent);">
                                <path d="M23 12s0-3.9-.5-5.8a3 3 0 00-2.1-2.1C18.5 3.6 12 3.6 12 3.6s-6.5 0-8.4.5A3 3 0 001.5 6.2C1 8.1 1 12 1 12s0 3.9.5 5.8a3 3 0 002.1 2.1c1.9.5 8.4.5 8.4.5s6.5 0 8.4-.5a3 3 0 002.1-2.1c.5-1.9.5-5.8.5-5.8zM9.8 15.4V8.6l5.9 3.4-5.9 3.4z"/>
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block font-medium">{{ $channelName }}</span>
                            @if (! empty($channel['blurb']))
                                <span class="block mt-0.5 text-sm leading-relaxed"
                                      style="color: var(--text-muted);">
                                    {{ $channel['blurb'] }}
                                </span>
                            @endif
                        </span>

                        <span class="shrink-0 text-xs" style="color: var(--text-faint);"
                              aria-hidden="true">↗</span>
                    </a>

                    @if (count($videos) > 0)
                        {{-- Thumbnails come straight from YouTube's image host,
                             so they follow the video rather than needing a
                             re-upload here whenever one is replaced. --}}
                        <ul class="grid sm:grid-cols-3 gap-4 mb-4">
                            @foreach ($videos as $video)
                                <li data-reveal>
                                    <a href="{{ $video['url'] }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="card overflow-hidden block h-full transition-colors">
                                        <img src="{{ $video['thumbnail'] }}"
                                             alt=""
                                             width="480" height="360"
                                             loading="lazy" decoding="async"
                                             class="w-full aspect-video object-cover"
                                             style="background: var(--bg-sunken);">

                                        <span class="block p-3 text-sm font-medium leading-snug">
                                            {{ $video['title'] }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif

                <ul class="grid sm:grid-cols-2 gap-4">
                    @foreach ($writing as $article)
                        <li data-reveal>
                            <a href="{{ $article['url'] }}"
                               target="_blank" rel="noopener noreferrer"
                               class="card p-5 h-full flex flex-col transition-colors">
                                <h3 class="font-medium leading-snug">
                                    {{ $article['title'] }}
                                </h3>

                                {{-- The article is in Arabic; on the English
                                     page a gloss says what it is without
                                     passing itself off as the real title. --}}
                                @if (! empty($article['gloss']))
                                    <p class="mt-1.5 text-sm leading-relaxed" style="color: var(--text-muted);">
                                        {{ $article['gloss'] }}
                                    </p>
                                @endif

                                <p class="mt-auto pt-4 text-xs flex items-center gap-2"
                                   style="color: var(--text-faint);">
                                    <span class="font-semibold" style="color: var(--accent);">
                                        {{ $article['publisher'] }}
                                    </span>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $article['date'] }}</span>
                                    <span class="ms-auto" aria-hidden="true">↗</span>
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ────────────────────────── Contact ────────────────────────── --}}
    <section id="contact" class="section relative border-t" style="border-color: var(--border);">
        <x-shapes :variant="1" />

        <div class="shell relative">
            <div class="grid lg:grid-cols-[1fr_1.1fr] gap-12 lg:gap-16">

                <div>
                    <x-section-heading :eyebrow="__('Contact')" :title="$talkHeading" />

                    <p class="text-base leading-relaxed" style="color: var(--text-muted);">
                        {{ $contact['pitch'] }}
                    </p>

                    <ul class="mt-8 space-y-3 text-sm">
                        <li>
                            <a href="mailto:{{ $identity['email'] }}"
                               class="font-medium" style="color: var(--accent);">
                                {{ $identity['email'] }}
                            </a>
                        </li>
                        <li>
                            <a href="tel:{{ str_replace(' ', '', $identity['phone']) }}"
                               class="font-medium" style="color: var(--accent);" dir="ltr">
                                {{ $identity['phone'] }}
                            </a>
                        </li>
                        @foreach ($socialLinks as $social)
                            <li>
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2"
                                   style="color: var(--text-muted);">
                                    <x-social-icon :name="$social['icon'] ?? ''" />
                                    {{ $social['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-6 text-sm" style="color: var(--text-faint);">
                        {{ $identity['location'] }}
                    </p>
                </div>

                <div class="card dotted p-6 sm:p-8">
                    @if (session('contact.sent'))
                        <div class="rounded-lg p-4 mb-6 text-sm font-medium"
                             style="background: var(--accent-soft); color: var(--accent);"
                             role="status">
                            {{ __("Thanks — your message is on its way. I'll reply shortly.") }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}#contact" class="space-y-5">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium mb-2">
                                {{ __('Your name') }}
                            </label>
                            <input type="text" id="name" name="name" required maxlength="120"
                                   value="{{ old('name') }}" autocomplete="name"
                                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                                   class="w-full rounded-lg px-4 py-3 text-sm border outline-none transition-colors"
                                   style="background: var(--bg); border-color: var(--border); color: var(--text);">
                            @error('name')
                                <p id="name-error" class="mt-1.5 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium mb-2">
                                {{ __('Your email') }}
                            </label>
                            <input type="email" id="email" name="email" required maxlength="190"
                                   value="{{ old('email') }}" autocomplete="email" dir="ltr"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                                   class="w-full rounded-lg px-4 py-3 text-sm border outline-none transition-colors"
                                   style="background: var(--bg); border-color: var(--border); color: var(--text);">
                            @error('email')
                                <p id="email-error" class="mt-1.5 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium mb-2">
                                {{ __('Your message') }}
                            </label>
                            <textarea id="message" name="message" rows="5" required minlength="10" maxlength="5000"
                                      @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                                      class="w-full rounded-lg px-4 py-3 text-sm border outline-none transition-colors resize-y"
                                      style="background: var(--bg); border-color: var(--border); color: var(--text);">{{ old('message') }}</textarea>
                            @error('message')
                                <p id="message-error" class="mt-1.5 text-xs" style="color: oklch(0.6 0.2 25);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Honeypot: hidden from humans, irresistible to bots. --}}
                        <div class="absolute w-px h-px -m-px overflow-hidden" aria-hidden="true">
                            <label for="website" tabindex="-1">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <button type="submit"
                                class="w-full px-6 py-3 rounded-full font-semibold transition-colors"
                                style="background: var(--accent-fill); color: var(--accent-text);"
                                onmouseover="this.style.background='var(--accent-fill-hover)'"
                                onmouseout="this.style.background='var(--accent-fill)'">
                            {{ __('Send message') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

</x-layout>
