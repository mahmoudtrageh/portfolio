<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Profile;
use Illuminate\Database\Migrations\Migration;

/*
| Seeds the sections behind the Home, Projects and Contact pages, so the
| simplified dashboard opens with the live copy already in it.
|
| A section that already has rows is left alone, so re-running never clobbers
| an edit. Identity is updated in place:
|   - role becomes "Software Engineer" (the browser-tab title)
|   - description is added for search results, if missing
|   - favicon is cleared, so the tab shows the cropped photo
|     (public/favicon-photo.png); uploading one in the dashboard overrides it.
*/
return new class extends Migration
{
    public function up(): void
    {
        $this->seedSingle('current_job', Profile::seedCurrent());
        $this->seedList('previous_jobs', Profile::seedPrevious());
        $this->seedList('job_projects', Profile::seedJobProjects());
        $this->seedList('freelance_projects', Profile::seedFreelanceProjects());

        $identity = ContentSection::query()->section('identity')->first();

        if ($identity !== null) {
            $data = $identity->data;

            $data['role'] = ['ar' => 'مهندس برمجيات', 'en' => 'Software Engineer'];
            $data['description'] ??= [
                'ar' => 'محمود طه، مهندس برمجيات في Boxes Intelligent Communications. أبني منتجات ويب آمنة وقابلة للتوسع.',
                'en' => 'Mahmoud Taha, software engineer at Boxes Intelligent Communications, building secure, scalable web products.',
            ];
            $data['favicon'] = '';

            $identity->update(['data' => $data]);
        }

        Content::flush();
    }

    public function down(): void
    {
        ContentSection::query()
            ->whereIn('section', ['current_job', 'previous_jobs', 'job_projects', 'freelance_projects'])
            ->delete();

        Content::flush();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function seedSingle(string $section, array $data): void
    {
        if (ContentSection::query()->section($section)->exists()) {
            return;
        }

        ContentSection::query()->create(['section' => $section, 'position' => 0, 'data' => $data]);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function seedList(string $section, array $rows): void
    {
        if (ContentSection::query()->section($section)->exists()) {
            return;
        }

        foreach ($rows as $position => $data) {
            ContentSection::query()->create(['section' => $section, 'position' => $position, 'data' => $data]);
        }
    }
};
