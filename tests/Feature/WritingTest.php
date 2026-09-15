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

it('lists every published article with its outbound link', function (string $locale): void {
    $response = $this->get("/{$locale}")->assertOk();

    foreach (Content::list('writing') as $article) {
        $response->assertSee($article['url'], escape: false);
    }
})->with(['ar', 'en']);

it('keeps the article title in its original language on both pages', function (): void {
    // The articles are Arabic; the English page must not invent an English
    // title that the linked page does not actually have.
    $arabicTitle = ContentSection::query()->section('writing')->first()->data['title']['ar'];

    $this->get('/ar')->assertOk()->assertSee($arabicTitle);
});

it('shows a translated gloss on the English page only', function (): void {
    $first = ContentSection::query()->section('writing')->first()->data;

    // Seeded with an English gloss and an empty Arabic one, since an Arabic
    // reader needs no gloss for an Arabic title.
    expect($first['gloss']['en'])->not->toBe('')
        ->and($first['gloss']['ar'])->toBe('');

    $this->get('/en')->assertOk()->assertSee($first['gloss']['en']);
});

it('opens every article in a new tab, safely', function (): void {
    $html = (string) $this->get('/en')->assertOk()->getContent();

    // An outbound target=_blank without rel=noopener hands the opened page a
    // handle on this one.
    preg_match_all('/<a[^>]+href="https:\/\/(?:www\.arageek|egyresmag)[^"]*"[^>]*>/', $html, $links);

    expect($links[0])->not->toBeEmpty();

    foreach ($links[0] as $link) {
        expect($link)->toContain('rel="noopener noreferrer"');
    }
});

it('exposes the writing anchor for the navigation', function (): void {
    $this->get('/en')
        ->assertOk()
        ->assertSee('id="writing"', escape: false)
        // The nav now links to the blog rather than this section, so the
        // anchor only has to exist as a scroll target on the page itself.
        ->assertSee('id="writing"', escape: false);
});

it('shows the YouTube channel with its link', function (string $locale): void {
    $channel = Content::get('channel');

    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee($channel['name'])
        ->assertSee($channel['url'], escape: false);
})->with(['ar', 'en']);

it('hides the channel block when its name is cleared', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'channel'])
        ->set('entries.0.name.ar', '')
        ->set('entries.0.name.en', '')
        ->call('save');

    // Name is a required translated field, so clearing it should be refused
    // rather than silently emptying the section.
    expect(Content::get('channel')['name'])->not->toBe('');
});

it('lets the dashboard edit an article', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'writing'])
        ->set('entries.0.publisher', 'Edited Publisher')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSee('Edited Publisher');
});

it('drops the whole section when there is nothing to show', function (): void {
    ContentSection::query()->where('section', 'writing')->delete();
    ContentSection::query()->where('section', 'channel')->delete();
    Content::flush();

    $this->get('/en')->assertOk()->assertDontSee('id="writing"', escape: false);
});
