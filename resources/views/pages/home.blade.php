@php
    use App\Support\Content;

    $identity = Content::get('identity');
    $photo = $identity['photo'] ?? '';
    $firstName = strtok((string) $identity['name'], ' ');
    $locale = app()->getLocale();

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
                            <li>{!! __('Previously :role at :company.', ['role' => e($job['role']), 'company' => $link($job['url'], $job['company'])]) !!}</li>
                        @endforeach
                    </ul>

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

            <div class="intro__photo fade-up">
                @if ($photo)
                    <img src="{{ Content::image($photo) }}" alt="{{ $identity['name'] }}"
                         width="640" height="480" loading="eager" decoding="async">
                @else
                    <span aria-hidden="true">{{ mb_substr((string) $identity['name'], 0, 1) }}</span>
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
