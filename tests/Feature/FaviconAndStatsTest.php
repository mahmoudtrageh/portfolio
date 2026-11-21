<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
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

it('falls back to the photo crop when none is uploaded', function (): void {
    expect(Content::get('identity')['favicon'])->toBe('');

    $this->get('/en')
        ->assertOk()
        ->assertSee('favicon-photo.png', escape: false)
        ->assertSee('apple-touch-icon-photo.png', escape: false);
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

it('counts companies from the experience timeline', function (): void {
    $roles = count(Content::list('timeline'));

    $this->get('/en')->assertOk()->assertSee((string) $roles);
});
