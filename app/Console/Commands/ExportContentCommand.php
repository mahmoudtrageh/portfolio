<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentSection;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Snapshots the dashboard content — every content_sections row plus the
 * uploaded files it points at — into database/seeders/content, so it can be
 * committed and loaded on another machine with SiteContentSeeder.
 */
final class ExportContentCommand extends Command
{
    protected $signature = 'content:export';

    protected $description = 'Save the dashboard content and its uploads for SiteContentSeeder';

    public function handle(): int
    {
        $dir = SiteContentSeeder::directory();
        $uploads = $dir.'/uploads';

        File::ensureDirectoryExists($dir);
        File::deleteDirectory($uploads);
        File::ensureDirectoryExists($uploads);

        $rows = ContentSection::query()
            ->orderBy('section')
            ->orderBy('position')
            ->get(['section', 'position', 'data'])
            ->map(fn (ContentSection $row): array => [
                'section' => $row->section,
                'position' => $row->position,
                'data' => $row->data,
            ])
            ->all();

        $disk = Storage::disk('public');
        $copied = 0;

        foreach (SiteContentSeeder::uploadsIn($rows) as $path) {
            if (! $disk->exists($path)) {
                $this->warn("Missing upload, skipped: {$path}");

                continue;
            }

            File::put($uploads.'/'.basename($path), (string) $disk->get($path));
            $copied++;
        }

        File::put(
            $dir.'/content.json',
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        );

        $this->info(sprintf('Exported %d rows and %d files to %s', count($rows), $copied, $dir));

        return self::SUCCESS;
    }
}
