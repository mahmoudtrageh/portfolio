<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use App\Support\Thumbnail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    Storage::fake('public');
    $this->admin = User::factory()->create();
});

/*
| Favicon
*/

it('falls back to the bundled icon when none is uploaded', function (): void {
    expect(Content::get('identity')['favicon'])->toBe('');

    $this->get('/en')
        ->assertOk()
        ->assertSee('favicon.ico', escape: false);
});

it('uses an uploaded favicon, with the right MIME type', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.favicon', UploadedFile::fake()->image('icon.png', 512, 512))
        ->call('save')
        ->assertHasNoErrors();

    $path = Content::get('identity')['favicon'];

    // The type attribute matters: without it a browser may fetch the icon and
    // then discard it.
    $this->get('/en')
        ->assertOk()
        ->assertSee('type="image/png" href="'.Content::image($path).'"', escape: false)
        ->assertDontSee('favicon.ico', escape: false);
});

it('also offers the icon to iOS home screens', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.favicon', UploadedFile::fake()->image('icon.png', 512, 512))
        ->call('save');

    $this->get('/en')->assertOk()->assertSee('apple-touch-icon', escape: false);
});

it('shows the favicon on the dashboard too', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.favicon', UploadedFile::fake()->image('icon.png', 512, 512))
        ->call('save');

    $this->actingAs($this->admin)->get('/admin')->assertOk()->assertSee('rel="icon"', escape: false);

    // The login page is only reachable as a guest — an authenticated request
    // is redirected to the dashboard.
    $this->post('/admin/logout');
    $this->get('/admin/login')->assertOk()->assertSee('rel="icon"', escape: false);
});

it('downscales an oversized favicon on upload', function (): void {
    // A 1254px icon drawn at 16px in a tab is bandwidth paid on every page view.
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.favicon', UploadedFile::fake()->image('huge.png', 1254, 1254))
        ->call('save')
        ->assertHasNoErrors();

    $path = Content::get('identity')['favicon'];
    $image = imagecreatefromstring(Storage::disk('public')->get($path));

    expect(imagesx($image))->toBeLessThanOrEqual(512)
        ->and(imagesy($image))->toBeLessThanOrEqual(512);
});

it('leaves an already-small image alone', function (): void {
    // Built in memory: a faked upload's temp file is gone by the time the
    // assertion runs.
    $image = imagecreatetruecolor(64, 64);
    ob_start();
    imagepng($image);
    $png = (string) ob_get_clean();
    imagedestroy($image);

    Storage::disk('public')->put('uploads/small.png', $png);

    $before = Storage::disk('public')->get('uploads/small.png');

    Thumbnail::shrink('uploads/small.png', 512);

    // Re-encoding a small image only loses quality, so it must be untouched.
    expect(Storage::disk('public')->get('uploads/small.png'))->toBe($before);
});

it('keeps the original when the file cannot be processed', function (): void {
    Storage::disk('public')->put('uploads/broken.png', 'not really an image');

    Thumbnail::shrink('uploads/broken.png', 512);

    Storage::disk('public')->assertExists('uploads/broken.png');
});

/*
| Hero stats
*/

it('shows exactly three hero figures', function (string $locale): void {
    $html = (string) $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('#<dl[^>]*>(.*?)</dl>#s', $html, $m);

    expect(substr_count($m[1] ?? '', '<dt'))->toBe(3);
})->with(['ar', 'en']);

it('counts projects from the content, without double-counting', function (): void {
    // Matx, Dsyncsolutions and Longimanus appear in both projects and
    // freelance; each is still one project.
    $names = collect(Content::list('projects'))
        ->concat(Content::list('other_projects'))
        ->concat(Content::list('freelance'))
        ->concat(Content::list('side_projects'))
        ->map(fn (array $p): string => mb_strtolower(trim((string) $p['name'])))
        ->filter();

    expect($names->unique()->count())->toBeLessThan($names->count());

    $this->get('/en')
        ->assertOk()
        ->assertSee($names->unique()->count().'+');
});

it('counts companies from the experience timeline', function (): void {
    $roles = count(Content::list('timeline'));

    $this->get('/en')->assertOk()->assertSee((string) $roles);
});

it('updates the counts when work is added', function (): void {
    $before = collect(Content::list('projects'))
        ->concat(Content::list('other_projects'))
        ->concat(Content::list('freelance'))
        ->concat(Content::list('side_projects'))
        ->map(fn (array $p): string => mb_strtolower(trim((string) $p['name'])))
        ->filter()->unique()->count();

    ContentSection::query()->create([
        'section' => 'side_projects',
        'position' => 99,
        'data' => [
            'name' => ['ar' => 'مشروع جديد', 'en' => 'A brand new project'],
            'tagline' => ['ar' => 'x', 'en' => 'x'],
            'summary' => ['ar' => 'x', 'en' => 'x'],
            'status' => ['ar' => '', 'en' => ''],
            'stack' => [],
            'url' => '',
        ],
    ]);

    Content::flush();

    // Derived, not typed: the figure moves on its own.
    $this->get('/en')->assertOk()->assertSee(($before + 1).'+');
});

it('writes the counts in Arabic-Indic numerals on the Arabic page', function (): void {
    $html = (string) $this->get('/ar')->assertOk()->getContent();

    preg_match('#<dl[^>]*>(.*?)</dl>#s', $html, $m);

    expect($m[1] ?? '')->toMatch('/[٠-٩]/u');
});

it('keeps the years figure editable in the dashboard', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'hero'])
        ->set('entries.0.experience.en', '7+')
        ->set('entries.0.experience.ar', '٧+')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSee('7+');
    $this->get('/ar')->assertOk()->assertSee('٧+');
});
