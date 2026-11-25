<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * The exported site content when a snapshot exists, otherwise the built-in
     * starting content. The admin account comes from `php artisan admin:create`.
     */
    public function run(): void
    {
        $this->call(is_file(SiteContentSeeder::directory().'/content.json')
            ? SiteContentSeeder::class
            : ContentSeeder::class);

        // Each only adds what is missing, so they are safe after either one.
        $this->call([
            SideProjectsSeeder::class,
            ProjectsSeeder::class,
        ]);
    }
}
