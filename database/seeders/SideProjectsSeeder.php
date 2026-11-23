<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Profile;
use Illuminate\Database\Seeder;

/**
 * Adds the side projects to the dashboard. Safe to run on a live site: it does
 * nothing once the section has any rows, so dashboard edits are never touched.
 *
 *     php artisan db:seed --class=SideProjectsSeeder --force
 */
final class SideProjectsSeeder extends Seeder
{
    public function run(): void
    {
        if (ContentSection::query()->section('side_projects')->exists()) {
            return;
        }

        foreach (Profile::seedSideProjects() as $position => $data) {
            ContentSection::query()->create([
                'section' => 'side_projects',
                'position' => $position,
                'data' => $data,
            ]);
        }

        Content::flush();
    }
}
