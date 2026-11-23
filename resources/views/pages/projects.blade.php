<x-layout :title="__('Projects')">
    {{-- Fits one desktop screen: the groups sit side by side as columns
         (stacked on smaller screens), each project a two-line row. --}}
    <section class="shell page page--fit">
        <header class="page__head fade-up">
            <h1 class="page__title">{{ __('Projects') }}</h1>
            <p class="page__lead">{{ __('A selection of the products I have built at work and as a freelancer.') }}</p>
        </header>

        <div class="projects fade-up">
            @foreach ($groups as $group)
                <section class="projects__group">
                    <div>
                        <h2 class="home-label">{{ $group['title'] }}</h2>
                        <p class="projects__lead">{{ $group['lead'] }}</p>
                    </div>

                    <ul class="projects__list">
                        @foreach ($group['items'] as $item)
                            <li>
                                @if ($item['url'] !== '')
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="projects__row">
                                        <span class="projects__name">{{ $item['name'] }}</span>
                                        <span class="projects__about">{{ $item['about'] }}</span>
                                        <span class="projects__arrow" aria-hidden="true">↗</span>
                                    </a>
                                @else
                                    <div class="projects__row">
                                        <span class="projects__name">{{ $item['name'] }}</span>
                                        <span class="projects__about">{{ $item['about'] }}</span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </section>
</x-layout>
