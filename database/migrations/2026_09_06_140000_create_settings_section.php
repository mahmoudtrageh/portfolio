<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Settings;
use Illuminate\Database\Migrations\Migration;

/*
| Creates the settings section and moves the blog switch into it.
|
| The blog switch shipped on `identity` first. Settings are not identity, and
| there are now three of them, so they get a section of their own. The existing
| value is carried across rather than reset, so a site that had deliberately
| switched the blog off stays that way.
|
| Every switch defaults to the permissive value, which is what the site did
| before any of them existed.
*/
return new class extends Migration
{
    public function up(): void
    {
        $identity = ContentSection::query()->section('identity')->first();

        // Whatever the blog was set to before, it stays set to.
        $blog = $identity !== null && array_key_exists('blog_enabled', $identity->data)
            ? (bool) $identity->data['blog_enabled']
            : true;

        if ($identity !== null) {
            $data = $identity->data;
            unset($data['blog_enabled']);

            $identity->update(['data' => $data]);
        }

        $defaults = [
            'blog_enabled' => $blog,
            'multilingual' => true,
            'primary_locale' => (string) config('app.locale'),
            'theme_toggle' => true,
            'theme' => 'light',
        ];

        $row = ContentSection::query()->section(Settings::SECTION)->first();

        // Anything already stored wins, so re-running never clobbers a choice.
        ContentSection::query()->updateOrCreate(
            ['section' => Settings::SECTION, 'position' => 0],
            ['data' => [...$defaults, ...($row->data ?? [])]],
        );

        Content::flush();
    }

    public function down(): void
    {
        $row = ContentSection::query()->section(Settings::SECTION)->first();

        // Put the blog switch back where it came from.
        if ($row !== null) {
            $identity = ContentSection::query()->section('identity')->first();

            $identity?->update(['data' => [
                ...$identity->data,
                'blog_enabled' => (bool) ($row->data['blog_enabled'] ?? true),
            ]]);

            $row->delete();
        }

        Content::flush();
    }
};
