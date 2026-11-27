<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentSection;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Safe to run on a live site: the full content (the exported snapshot when
     * one exists, otherwise the built-in starting content) is only loaded into
     * an empty database. Once there is content, only the seeders that add what
     * is missing run, so dashboard edits are never replaced.
     *
     * To deliberately replace everything with the snapshot:
     *
     *     php artisan db:seed --class=SiteContentSeeder --force
     *
     * The admin account comes from `php artisan admin:create`.
     */
    public function run(): void
    {
        if (! ContentSection::query()->exists()) {
            $this->call(is_file(SiteContentSeeder::directory().'/content.json')
                ? SiteContentSeeder::class
                : ContentSeeder::class);
        }

        // Each only adds what is missing.
        $this->call([
            SideProjectsSeeder::class,
            ProjectsSeeder::class,
        ]);
    }
}
