<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('drops the whole section when there is nothing to show', function (): void {
    ContentSection::query()->where('section', 'writing')->delete();
    ContentSection::query()->where('section', 'channel')->delete();
    Content::flush();

    $this->get('/en')->assertOk()->assertDontSee('id="writing"', escape: false);
});
