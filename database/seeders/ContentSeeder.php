<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentSection;
use App\Support\Content;
use Illuminate\Database\Seeder;

/**
 * Imports config/portfolio.php into content_sections.
 *
 * The config file stays in the repo as the canonical starting point; this moves
 * it into the database once so the dashboard has something to edit. Re-running
 * it replaces the stored content, so it doubles as a reset to the seeded state.
 */
final class ContentSeeder extends Seeder
{
    /**
     * Config keys that are settings rather than editable content, and so are
     * never imported.
     *
     * @var list<string>
     */
    private const SKIP = ['locales', 'dir', 'locale_names'];

    public function run(): void
    {
        /** @var array<string, mixed> $portfolio */
        $portfolio = config('portfolio', []);

        ContentSection::query()->delete();

        foreach ($portfolio as $section => $value) {
            if (in_array($section, self::SKIP, true) || ! is_array($value)) {
                continue;
            }

            // Singletons store one row; list sections store one row per entry,
            // numbered so the dashboard can reorder them.
            $entries = in_array($section, Content::SINGLETONS, true)
                ? [$value]
                : array_values($value);

            foreach ($entries as $position => $entry) {
                ContentSection::query()->create([
                    'section' => $section,
                    'position' => $position,
                    'data' => $entry,
                ]);
            }
        }

        Content::flush();
    }
}
