@php
    use App\Support\Content;
    use App\Support\Profile;

    $identity = Content::get('identity');
    $photo = $identity['photo'] ?? '';
    // Real dimensions, so the browser reserves the photo's true shape.
    $photoSize = $photo !== '' ? @getimagesize(storage_path('app/public/'.$photo)) : false;
    $firstName = strtok((string) $identity['name'], ' ');
    $locale = app()->getLocale();

    // All optional (dashboard → Profile); the caption hides when all are empty.
    $age = Profile::age();
    $experience = Profile::experience();
    $location = Profile::location();
    // Every project on the Projects page; follows the dashboard lists.
    $projects = Profile::projectCountLabel();
    $techStack = Profile::techStack();

    // Managed in the dashboard (Profile → CV). An uploaded file is a stored
    // path; a legacy value may be a full URL. With nothing set, the link hides.
    $cvValue = trim((string) ($identity['cv'] ?? ''));
    $cv = str_starts_with($cvValue, 'uploads/') ? Content::image($cvValue) : $cvValue;

    $link = fn (string $url, string $text): string => $url === ''
        ? '<span class="intro-strong">'.e($text).'</span>'
        : '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="intro-link">'.e($text).'</a>';
@endphp

<x-layout>
    <section class="shell intro">
        <div class="intro__grid">
            <div class="fade-up">
                <h1 class="intro__title">
                    <span class="intro__hello">{{ __('Hello.') }}</span>
                    <span class="block">{{ __('I’m :name.', ['name' => $firstName]) }}</span>
                </h1>

                <p class="intro__job">
                    {!! __('I’m a :role at :company.', [
                        // The job title from the dashboard, lower-cased mid-sentence
                        // in English ("I'm a software engineer at …").
                        'role' => '<span class="intro-accent">'.e($locale === 'en' ? mb_strtolower((string) $identity['role']) : (string) $identity['role']).'</span>',
                        'company' => $link($current['url'], $current['company']),
                    ]) !!}
                    @if ($current['work_mode'] !== '')
                        <span class="intro__mode">({{ $current['work_mode'] }})</span>
                    @endif
                </p>

                <div class="intro__body">
                    <p>
                        {{ __('We work on') }}
                        @foreach ($current['products'] as $product)
                            {!! $link($product['url'], $product['name']) !!}, {{ $product['about'] }}{{ $loop->last ? '.' : ($loop->remaining === 1 ? ' '.__('and') : ',') }}
                        @endforeach
                    </p>

                    <ul class="intro__previous">
                        @foreach ($previous as $job)
                            <li>
                                {!! __('Previously :role at :company.', ['role' => e($job['role']), 'company' => $link($job['url'], $job['company'])]) !!}
                                @if ($job['work_mode'] !== '')
                                    <span class="intro__mode">({{ $job['work_mode'] }})</span>
                                @endif
                                @if ($job['year'] !== '')
                                    <span class="intro__mode">· {{ $job['year'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    {{-- Programme alumni line, set apart from the jobs above. The
                         name links to the certificate scan, opened in the shared
                         lightbox. --}}
                    @php($m2gate = file_exists(storage_path('app/public/uploads/cert-m2gate.png')) ? Content::image('uploads/cert-m2gate.png') : '')
                    <p class="intro__alumni">
                        {!! __('Alumnus of :programme, an entrepreneurship programme by the William Davidson Institute at the University of Michigan.', [
                            'programme' => $m2gate === ''
                                ? '<span class="intro-strong">M²GATE</span>'
                                : '<a href="'.e($m2gate).'" class="intro-link" x-data x-on:click.prevent="$dispatch(\'lightbox\', { src: \''.e($m2gate).'\', caption: \'M²GATE\' })">M²GATE</a>',
                        ]) !!}
                        <span class="intro__mode">· {{ Profile::year('2018') }}</span>
                    </p>

                    @if ($techStack !== [])
                        <ul class="intro__tags" aria-label="{{ __('Tech stack') }}">
                            @foreach ($techStack as $tech)
                                <li class="intro__tag">{{ $tech }}</li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>

            <div class="intro__photo fade-up">
                <figure class="intro__figure">
                    @if ($photo)
                        <img src="{{ Content::image($photo) }}" alt="{{ $identity['name'] }}"
                             @if ($photoSize) width="{{ $photoSize[0] }}" height="{{ $photoSize[1] }}" @endif
                             loading="eager" decoding="async">
                    @else
                        <span aria-hidden="true">{{ mb_substr((string) $identity['name'], 0, 1) }}</span>
                    @endif

                    @if ($location !== '' || $age !== null || $experience !== null || $projects !== '')
                        <figcaption class="intro__caption">
                            @if ($location !== '')
                                <span class="intro__caption-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 21s-7-6.2-7-11.5A7 7 0 0119 9.5C19 14.8 12 21 12 21z"/>
                                        <circle cx="12" cy="9.5" r="2.5"/>
                                    </svg>
                                    {{ $location }}
                                </span>
                            @endif
                            @if ($location !== '' && $age !== null)
                                <span class="intro__caption-dot" aria-hidden="true">·</span>
                            @endif
                            @if ($age !== null)
                                <span class="intro__caption-item">{{ __(':age years old', ['age' => $age]) }}</span>
                            @endif
                            {{-- Work facts on a line of their own: too long to
                                 share one with the location and age on a
                                 narrow photo. --}}
                            @if ($experience !== null || $projects !== '')
                                <span class="intro__caption-item intro__caption-line">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="7" width="18" height="13" rx="2"/>
                                        <path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M3 13h18"/>
                                    </svg>
                                    @if ($experience !== null)
                                        {{ __(':years yrs experience', ['years' => $experience]) }}
                                    @endif
                                    @if ($experience !== null && $projects !== '')
                                        <span class="intro__caption-dot" aria-hidden="true">·</span>
                                    @endif
                                    @if ($projects !== '')
                                        {{ $projects }}
                                    @endif
                                </span>
                            @endif
                        </figcaption>
                    @endif
                </figure>

                {{-- Under the photo rather than at the end of the text, which is
                     the taller column; keeps the intro within one screen. --}}
                @if ($cv !== '')
                    <a href="{{ $cv }}" target="_blank" rel="noopener" class="intro-cv">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                        </svg>
                        {{ __('Download my CV') }}
                    </a>
                @endif
            </div>
        </div>

        @if ($posts->isNotEmpty())
            <div id="blog" class="intro__posts">
                <div class="flex items-baseline justify-between gap-4 mb-2">
                    <h2 class="home-label">{{ __('Latest writing') }}</h2>
                    <a href="{{ route('blog', ['locale' => $locale]) }}" target="_blank" class="text-sm intro-link">{{ __('All posts') }}</a>
                </div>
                <ul>
                    @foreach ($posts as $post)
                        <li class="border-b" style="border-color: var(--border);">
                            <a href="{{ route('post', ['locale' => $locale, 'slug' => $post->slug($locale)]) }}" target="_blank" class="home-row">
                                <span class="font-medium">{{ $post->title($locale) }}</span>
                                <span class="home-row__about">{{ $post->excerpt($locale, 90) }}</span>
                                <span class="home-row__arrow" aria-hidden="true">→</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
</x-layout>
