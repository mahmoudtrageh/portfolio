<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;
use Illuminate\Database\Migrations\Migration;

/*
| Backfills the blog switch onto the stored identity section.
|
| The dashboard fills a missing field in from the schema when it renders, and
| for a bool that fill-in is `false`. So without this the checkbox would show
| the blog as OFF while the site still served it — and the first unrelated save
| to Identity would persist that false and take the blog down by accident.
|
| Writing `true` here settles it: an existing site keeps the blog it already
| had, and the checkbox agrees with what visitors see. Sites installed after
| this point get the same value from ContentSeeder via config/portfolio.php.
*/
return new class extends Migration
{
    public function up(): void
    {
        foreach (ContentSection::query()->section('identity')->get() as $row) {
            // Anyone who has already set it keeps their choice.
            if (array_key_exists('blog_enabled', $row->data)) {
                continue;
            }

            $row->update(['data' => [...$row->data, 'blog_enabled' => true]]);
        }

        Content::flush();
    }

    public function down(): void
    {
        foreach (ContentSection::query()->section('identity')->get() as $row) {
            $data = $row->data;
            unset($data['blog_enabled']);

            $row->update(['data' => $data]);
        }

        Content::flush();
    }
};
