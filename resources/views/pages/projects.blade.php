<x-layout :title="__('Projects')">
    <section class="shell page">
        <header class="page__head fade-up">
            <h1 class="page__title">{{ __('Projects') }}</h1>
            <p class="page__lead">{{ __('A selection of the products I have built at work and as a freelancer.') }}</p>
        </header>

        <div class="space-y-16 fade-up">
            @foreach ($groups as $group)
                <div class="home-grid">
                    <div>
                        <h2 class="home-label">{{ $group['title'] }}</h2>
                        <p class="mt-3 text-sm leading-relaxed" style="color: var(--text-faint);">{{ $group['lead'] }}</p>
                    </div>

                    <ul class="border-t" style="border-color: var(--border);">
                        @foreach ($group['items'] as $item)
                            <li class="border-b" style="border-color: var(--border);">
                                @if ($item['url'] !== '')
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="home-row">
                                        <span class="font-medium">{{ $item['name'] }}</span>
                                        <span class="home-row__about">{{ $item['about'] }}</span>
                                        <span class="home-row__arrow" aria-hidden="true">↗</span>
                                    </a>
                                @else
                                    <div class="home-row">
                                        <span class="font-medium">{{ $item['name'] }}</span>
                                        <span class="home-row__about">{{ $item['about'] }}</span>
                                        <span class="home-row__arrow" aria-hidden="true"></span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
</x-layout>
