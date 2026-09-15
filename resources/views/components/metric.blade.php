{{--
    Renders text that may contain «placeholder» markers.

    Unverified figures are wrapped in «guillemets» in config/portfolio.php.
    This component highlights them so an unfilled number can never quietly ship
    as if it were real. Once every « » is replaced with a verified figure, this
    component renders plain text and the highlighting disappears on its own.
--}}
@props(['text'])

@php
    $parts = preg_split('/(«[^»]*»)/u', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
@endphp

<span {{ $attributes }}>
    @foreach ($parts as $part)
        @if (str_starts_with($part, '«'))
            <span class="placeholder" title="Unverified value — replace in config/portfolio.php">{{ trim($part, '«»') }}</span>
        @else
            {{ $part }}
        @endif
    @endforeach
</span>
