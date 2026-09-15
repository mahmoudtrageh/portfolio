{{-- The "opens in a new tab" arrow.

     An SVG rather than the "↗" character: that codepoint renders as a colour
     emoji on Windows and Android, which ignores the surrounding text colour
     and clashes with the accent. This inherits it via currentColor. --}}

@props(['size' => 'w-3.5 h-3.5'])

<svg {{ $attributes->merge(['class' => $size.' shrink-0 inline-block']) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M7 17 17 7M9 7h8v8"/>
</svg>
