<?php

declare(strict_types=1);

use App\Models\ContentSection;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

it('finds every upload path referenced in the rows', function (): void {
    $rows = [
        ['section' => 'identity', 'position' => 0, 'data' => ['photo' => 'uploads/a.jpg', 'name' => ['en' => 'x']]],
        ['section' => 'certificates', 'position' => 0, 'data' => ['image' => 'uploads/b.png', 'url' => 'https://e.com/uploads/c']],
    ];

    expect(SiteContentSeeder::uploadsIn($rows))->toBe(['uploads/a.jpg', 'uploads/b.png']);
});

it('round-trips the content and its uploads through export and seed', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('uploads/photo.jpg', 'jpeg-bytes');

    $dir = SiteContentSeeder::directory();
    $backup = storage_path('framework/testing/content-backup');
    File::deleteDirectory($backup);

    if (File::isDirectory($dir)) {
        File::copyDirectory($dir, $backup);
    }

    try {
        ContentSection::query()->section('identity')->first()?->update([
            'data' => ['name' => ['en' => 'Seeded Name', 'ar' => 'اسم'], 'photo' => 'uploads/photo.jpg'],
        ]);

        $this->artisan('content:export')->assertSuccessful();

        ContentSection::query()->delete();
        Storage::disk('public')->delete('uploads/photo.jpg');

        $this->seed(SiteContentSeeder::class);

        expect(ContentSection::query()->section('identity')->first()?->data['name']['en'])->toBe('Seeded Name')
            ->and(Storage::disk('public')->get('uploads/photo.jpg'))->toBe('jpeg-bytes');
    } finally {
        File::deleteDirectory($dir);

        if (File::isDirectory($backup)) {
            File::copyDirectory($backup, $dir);
            File::deleteDirectory($backup);
        }
    }
});
