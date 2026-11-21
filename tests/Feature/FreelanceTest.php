<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('falls back to a plain timeline when there is no freelance work', function (): void {
    ContentSection::query()->where('section', 'freelance')->delete();
    Content::flush();

    $this->get('/en')
        ->assertOk()
        ->assertDontSee('role="tablist"', escape: false)
        ->assertSee('Boxes Intelligent Communications');
});
