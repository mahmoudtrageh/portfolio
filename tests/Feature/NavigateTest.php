<?php

declare(strict_types=1);

/*
| Internal links swap pages in place through Livewire's wire:navigate.
*/

it('loads Livewire on public pages and navigates internal links in place', function (): void {
    $this->get('/en')
        ->assertOk()
        ->assertSee('window.livewireScriptConfig', escape: false)
        ->assertSee('data-navigate-track="reload"', escape: false)
        ->assertSee('class="nav-link', escape: false)
        ->assertSee('wire:navigate', escape: false);
});

it('keeps the language switcher a full page load', function (): void {
    // Switching language changes <html lang> and dir, which a swap keeps.
    $html = $this->get('/en')->assertOk()->getContent();

    preg_match('#<a href="[^"]*"\s+hreflang="ar"[^>]*>#', (string) $html, $switcher);

    expect($switcher)->not->toBeEmpty()
        ->and($switcher[0])->not->toContain('wire:navigate');
});
