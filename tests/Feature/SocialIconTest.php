<?php

declare(strict_types=1);

it('renders a brand mark for each known social icon', function (): void {
    $html = Blade::render('<x-social-icon name="github" /><x-social-icon name="linkedin" /><x-social-icon name="youtube" />');

    expect(substr_count($html, '<svg'))->toBe(3);
});

it('falls back to the arrow for an unknown icon', function (): void {
    // The fallback is an SVG arrow rather than the "↗" character, which
    // renders as a colour emoji and ignores the surrounding text colour.
    expect(Blade::render('<x-social-icon name="myspace" />'))
        ->toContain('<svg')
        ->toContain('M7 17 17 7M9 7h8v8')
        ->not->toContain('↗');
});

it('shows the icons on the live page', function (): void {
    $this->get('/en')->assertOk()->assertSee('viewBox="0 0 24 24"', escape: false);
});
