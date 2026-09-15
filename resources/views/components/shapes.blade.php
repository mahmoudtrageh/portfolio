{{--
    Scattered geometric confetti in the page margins.

    Purely decorative: aria-hidden, pointer-events-none, and hidden below `lg`
    where there are no margins wide enough to hold it without crowding the text.
    Positions are absolute against the nearest positioned ancestor — put this
    inside a `relative` section.
--}}
@props(['variant' => 1])

@php
    // Each variant is a hand-placed arrangement so no two sections repeat.
    $sets = [
        1 => [
            ['shape' => 'hexagon', 'color' => 'var(--shape-blue)',   'size' => 22, 'top' => '18%', 'start' => '3%'],
            ['shape' => 'diamond', 'color' => 'var(--shape-orange)', 'size' => 18, 'top' => '8%',  'end' => '5%'],
            ['shape' => 'circle',  'color' => 'var(--shape-pink)',   'size' => 20, 'top' => '62%', 'end' => '2%'],
            ['shape' => 'triangle','color' => 'var(--shape-green)',  'size' => 16, 'top' => '78%', 'start' => '6%'],
        ],
        2 => [
            ['shape' => 'triangle','color' => 'var(--shape-orange)', 'size' => 18, 'top' => '12%', 'start' => '4%'],
            ['shape' => 'circle',  'color' => 'var(--shape-blue)',   'size' => 16, 'top' => '55%', 'start' => '2%'],
            ['shape' => 'diamond', 'color' => 'var(--shape-green)',  'size' => 20, 'top' => '30%', 'end' => '3%'],
        ],
        3 => [
            ['shape' => 'diamond', 'color' => 'var(--shape-pink)',   'size' => 18, 'top' => '20%', 'end' => '4%'],
            ['shape' => 'hexagon', 'color' => 'var(--shape-green)',  'size' => 20, 'top' => '70%', 'start' => '3%'],
        ],
    ];

    $items = $sets[$variant] ?? $sets[1];
@endphp

<div class="pointer-events-none absolute inset-0 hidden lg:block overflow-hidden" aria-hidden="true">
    @foreach ($items as $item)
        @php
            $style = 'top: '.$item['top'].';';
            $style .= isset($item['start']) ? 'inset-inline-start: '.$item['start'].';' : '';
            $style .= isset($item['end']) ? 'inset-inline-end: '.$item['end'].';' : '';
        @endphp

        <span class="absolute" style="{{ $style }}">
            <svg width="{{ $item['size'] }}" height="{{ $item['size'] }}" viewBox="0 0 24 24"
                 fill="{{ $item['color'] }}" focusable="false">
                @switch ($item['shape'])
                    @case('hexagon')
                        <path d="M12 1.8l8.8 5.1v10.2L12 22.2 3.2 17.1V6.9z"/>
                        @break
                    @case('diamond')
                        <rect x="12" y="0.5" width="16.3" height="16.3" rx="2"
                              transform="rotate(45 12 0.5)"/>
                        @break
                    @case('triangle')
                        <path d="M12 3l9.5 18H2.5z"/>
                        @break
                    @default
                        <circle cx="12" cy="12" r="10"/>
                @endswitch
            </svg>
        </span>
    @endforeach
</div>
