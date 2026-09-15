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

it('splits experience into employment and freelancing tabs', function (string $locale): void {
    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee('role="tablist"', escape: false)
        ->assertSee(__('Employment', [], $locale))
        ->assertSee(__('Freelancing', [], $locale));
})->with(['ar', 'en']);

it('lists every freelance project in the tab', function (string $locale): void {
    $response = $this->get("/{$locale}")->assertOk();

    foreach (Content::list('freelance') as $job) {
        $response->assertSee($job['name']);
    }
})->with(['ar', 'en']);

it('keeps both panels in the markup so the content is indexable', function (): void {
    // Tabs are a display concern. Rendering only the active panel would hide
    // the freelance work from crawlers and from anyone without JavaScript.
    $html = (string) $this->get('/en')->assertOk()->getContent();

    expect(substr_count($html, 'role="tabpanel"'))->toBe(2)
        ->and($html)->toContain('Course registration with online payment')
        ->and($html)->toContain('Boxes Intelligent Communications');
});

it('shows the client rating and review on a rated project', function (): void {
    $rated = collect(Content::list('freelance'))
        ->first(fn (array $j): bool => trim((string) ($j['rating'] ?? '')) !== '');

    expect($rated)->not->toBeNull();

    $this->get('/en')
        ->assertOk()
        ->assertSee($rated['client'])
        ->assertSee(__(':rating out of 5', ['rating' => (float) $rated['rating']]));
});

it('omits the stars on a project with no rating', function (): void {
    // Entries carried over from elsewhere have no client review attached.
    $unrated = collect(Content::list('freelance'))
        ->first(fn (array $j): bool => trim((string) ($j['rating'] ?? '')) === '');

    expect($unrated)->not->toBeNull();

    $html = (string) $this->get('/en')->assertOk()->getContent();

    $at = strpos($html, e($unrated['name']));
    expect($at)->not->toBeFalse();

    // No star markup inside that card's own block.
    expect(substr($html, $at, 700))->not->toContain('out of 5');
});

it('marks an Arabic review rtl even on the English page', function (): void {
    // Reviews are quoted as the client wrote them; without dir the punctuation
    // drifts to the wrong end of the line.
    $this->get('/en')
        ->assertOk()
        ->assertSee('dir="rtl"', escape: false);
});

it('falls back to a plain timeline when there is no freelance work', function (): void {
    ContentSection::query()->where('section', 'freelance')->delete();
    Content::flush();

    $this->get('/en')
        ->assertOk()
        ->assertDontSee('role="tablist"', escape: false)
        ->assertSee('Boxes Intelligent Communications');
});

it('lets the dashboard edit a freelance entry', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'freelance'])
        ->set('entries.0.platform', 'Upwork')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSee('Upwork');
});

it('serves the freelance section in the dashboard', function (): void {
    $this->actingAs($this->admin)->get('/admin/freelance')->assertOk();
});

it('puts freelancing in the Work sidebar group', function (): void {
    $work = collect(config('dashboard.groups'))->firstWhere('label.en', 'Work');

    expect($work['sections'])->toContain('freelance');
});
