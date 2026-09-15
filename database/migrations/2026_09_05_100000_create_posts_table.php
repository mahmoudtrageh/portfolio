<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Blog posts.
|
| Unlike content_sections, a post is addressable: it has a per-locale slug, its
| own publish state, and a publication date the feed and sitemap order by. That
| does not fit the section model, so posts get their own table.
|
| The bilingual columns are suffixed per locale rather than nested in JSON so
| the database can index and filter on them — the blog index queries "published
| in this locale", which a JSON blob cannot answer efficiently.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();

            // Slugs are per-locale so each language gets a readable URL.
            $table->string('slug_ar')->nullable()->unique();
            $table->string('slug_en')->nullable()->unique();

            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();

            // Short summary for cards, meta description and the RSS feed.
            $table->text('excerpt_ar')->nullable();
            $table->text('excerpt_en')->nullable();

            // Markdown source. Rendered HTML is derived, never stored.
            $table->longText('body_ar')->nullable();
            $table->longText('body_en')->nullable();

            // A post can go live in one language before the other.
            $table->boolean('published_ar')->default(false);
            $table->boolean('published_en')->default(false);

            $table->string('cover')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // The blog index filters by locale publish state and orders by date.
            $table->index(['published_ar', 'published_at']);
            $table->index(['published_en', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
