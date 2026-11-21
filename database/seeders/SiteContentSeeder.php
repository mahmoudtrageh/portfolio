<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentSection;
use App\Support\Content;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Loads the snapshot written by `php artisan content:export`: replaces every
 * content_sections row and puts the uploaded files (photo, favicon, CV, …) on
 * the public disk. Run it anywhere to get the same site as the exporting
 * machine:
 *
 *     php artisan db:seed --class=SiteContentSeeder --force
 *
 * Users, messages and blog posts are left alone.
 */
final class SiteContentSeeder extends Seeder
{
    public static function directory(): string
    {
        return database_path('seeders/content');
    }

    public function run(): void
    {
        $file = self::directory().'/content.json';

        if (! is_file($file)) {
            throw new RuntimeException("No snapshot at {$file}. Run `php artisan content:export` first.");
        }

        /** @var list<array{section: string, position: int, data: array<string, mixed>}> $rows */
        $rows = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);

        $disk = Storage::disk('public');

        foreach (self::uploadsIn($rows) as $path) {
            $source = self::directory().'/uploads/'.basename($path);

            if (is_file($source)) {
                $disk->put($path, (string) file_get_contents($source));
            }
        }

        ContentSection::query()->delete();

        foreach ($rows as $row) {
            ContentSection::query()->create([
                'section' => $row['section'],
                'position' => $row['position'],
                'data' => $row['data'],
            ]);
        }

        Content::flush();
    }

    /**
     * Every stored upload path ("uploads/…") referenced anywhere in the rows.
     *
     * @param  array<mixed>  $rows
     * @return list<string>
     */
    public static function uploadsIn(array $rows): array
    {
        $found = [];

        foreach ($rows as $value) {
            if (is_array($value)) {
                $found = [...$found, ...self::uploadsIn($value)];
            } elseif (is_string($value) && preg_match('#^uploads/[^/]+$#', $value) === 1) {
                $found[] = $value;
            }
        }

        return array_values(array_unique($found));
    }
}
