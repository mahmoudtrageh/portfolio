<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| The site's content, moved out of config/portfolio.php so the dashboard can
| edit it without rewriting a PHP file.
|
| One row per entry. A section is either a singleton (identity, hero, about,
| contact — exactly one row) or an ordered list (projects, timeline, skills —
| many rows, ordered by `position`). `data` holds the entry itself, with the
| same ['ar' => ..., 'en' => ...] shape the config used, so App\Support\Content
| keeps working unchanged.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('section')->index();
            $table->unsignedInteger('position')->default(0);
            $table->json('data');
            $table->timestamps();

            // Rows within a section are reordered as a unit, so this is the
            // index every read and every drag-to-reorder write goes through.
            $table->index(['section', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_sections');
    }
};
