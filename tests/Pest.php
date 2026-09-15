<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Settings;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
| Feature tests run against a fresh in-memory database seeded from
| config/portfolio.php, so the content the site renders in tests is the same
| content ContentSeeder installs on a real machine.
*/
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->seed(ContentSeeder::class);
    })
    ->in('Feature');

/**
 * Write one site setting and drop the cached content tree.
 */
function setSetting(string $key, mixed $value): void
{
    $row = ContentSection::query()->section(Settings::SECTION)->first();

    ContentSection::query()->updateOrCreate(
        ['section' => Settings::SECTION, 'position' => 0],
        ['data' => [...($row->data ?? []), $key => $value]],
    );

    Content::flush();
}

/**
 * Remove the settings row entirely, so every switch falls back to its default.
 */
function forgetSettings(): void
{
    ContentSection::query()->where('section', Settings::SECTION)->delete();

    Content::flush();
}
