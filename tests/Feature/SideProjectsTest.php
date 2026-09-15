<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('shows every side project with its link', function (string $locale): void {
    $response = $this->get("/{$locale}")->assertOk();

    foreach (Content::list('side_projects') as $side) {
        $response->assertSee($side['name'])->assertSee($side['url'], escape: false);
    }
})->with(['ar', 'en']);

it('shows a status badge when one is set', function (): void {
    $this->get('/en')->assertOk()->assertSee('In development');
});

it('hides the side-projects block when there are none', function (): void {
    ContentSection::query()->where('section', 'side_projects')->delete();
    Content::flush();

    $this->get('/en')->assertOk()->assertDontSee(__('Side projects'));
});

it('links the product shipped in a role from its timeline entry', function (): void {
    $mawaheb = collect(Content::list('timeline'))
        ->first(fn (array $r): bool => str_contains($r['org'], 'MAWAHEB'));

    expect($mawaheb['product_url'] ?? '')->not->toBe('');

    $this->get('/en')
        ->assertOk()
        ->assertSee($mawaheb['product'])
        ->assertSee($mawaheb['product_url'], escape: false);
});

it('omits the product line on roles that shipped no named product', function (): void {
    // Content resolves to the app's default locale, so read the org name in
    // the locale actually being requested.
    app()->setLocale('en');

    $without = collect(Content::list('timeline'))
        ->first(fn (array $r): bool => trim((string) ($r['product'] ?? '')) === '');

    expect($without)->not->toBeNull();

    $html = (string) $this->get('/en')->assertOk()->getContent();

    // No product link inside that role's own block.
    $at = strpos($html, e($without['org']));
    expect($at)->not->toBeFalse()
        ->and(substr($html, $at, 400))->not->toContain('↗');
});

it('opens every external project link safely', function (): void {
    $html = (string) $this->get('/en')->assertOk()->getContent();

    // target=_blank without rel=noopener hands the opened page a handle on
    // this one.
    preg_match_all('/<a\s[^>]*target="_blank"[^>]*>/s', $html, $links);

    expect($links[0])->not->toBeEmpty();

    foreach ($links[0] as $link) {
        expect($link)->toContain('rel="noopener noreferrer"');
    }
});

it('lists Woosh under freelancing', function (): void {
    $names = collect(Content::list('freelance'))->pluck('name')->all();

    expect($names)->toContain('Woosh');
});

it('lets the dashboard edit a side project', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'side_projects'])
        ->set('entries.0.url', 'https://example.test/')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSee('https://example.test/', escape: false);
});

it('serves the side-projects section in the dashboard', function (): void {
    $this->actingAs($this->admin)->get('/admin/side_projects')->assertOk();
});
