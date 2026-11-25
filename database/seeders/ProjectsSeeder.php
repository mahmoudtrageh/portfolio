<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentSection;
use App\Support\Content;
use Illuminate\Database\Seeder;

/**
 * Adds newer projects to the Projects page: each goes to the end of its
 * section unless a project with the same English name is already there. Safe
 * to run on a live site; existing dashboard rows are never changed.
 *
 *     php artisan db:seed --class=ProjectsSeeder --force
 */
final class ProjectsSeeder extends Seeder
{
    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function projects(): array
    {
        return [
            'freelance_projects' => [
                ['name' => ['ar' => 'Longimanus Liveaboard', 'en' => 'Longimanus Liveaboard'], 'url' => '', 'about' => ['ar' => 'Backend لمركز غوص', 'en' => 'The backend for a diving center']],
            ],
            'job_projects' => [
                ['name' => ['ar' => 'Larabuilder', 'en' => 'Larabuilder'], 'url' => '', 'about' => ['ar' => 'تحسين أداء منصة لبناء المواقع', 'en' => 'Performance work on a website builder']],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::projects() as $section => $projects) {
            $rows = ContentSection::query()->section($section)->get();

            $names = $rows->map(fn (ContentSection $row): string => mb_strtolower(trim((string) ($row->data['name']['en'] ?? ''))))->all();
            $position = $rows->isEmpty() ? 0 : (int) $rows->max('position') + 1;

            foreach ($projects as $data) {
                if (in_array(mb_strtolower($data['name']['en']), $names, true)) {
                    continue;
                }

                ContentSection::query()->create([
                    'section' => $section,
                    'position' => $position++,
                    'data' => $data,
                ]);
            }
        }

        Content::flush();
    }
}
