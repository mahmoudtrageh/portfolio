<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Profile;
use Illuminate\Database\Migrations\Migration;

/*
| Seeds the home page's tech tags, so the dashboard opens with
| the live list already in it. Left alone if the section already has a row.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (! ContentSection::query()->section('tech_stack')->exists()) {
            ContentSection::query()->create([
                'section' => 'tech_stack',
                'position' => 0,
                'data' => ['items' => Profile::seedTechStack()],
            ]);
        }

        Content::flush();
    }

    public function down(): void
    {
        ContentSection::query()->section('tech_stack')->delete();

        Content::flush();
    }
};
