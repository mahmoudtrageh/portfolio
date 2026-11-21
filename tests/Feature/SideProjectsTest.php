<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('hides the side-projects block when there are none', function (): void {
    ContentSection::query()->where('section', 'side_projects')->delete();
    Content::flush();

    $this->get('/en')->assertOk()->assertDontSee(__('Side projects'));
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
