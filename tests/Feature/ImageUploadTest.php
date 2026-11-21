<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\User;
use App\Support\Content;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    Storage::fake('public');
    $this->admin = User::factory()->create();
});

/**
 * Upload an image into one of identity's two image fields and save.
 */
function uploadIdentityImage(User $admin, string $field, ?UploadedFile $file = null): string
{
    Livewire::actingAs($admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set("uploads.0.{$field}", $file ?? UploadedFile::fake()->image('pic.png', 200, 200))
        ->call('save')
        ->assertHasNoErrors();

    return (string) Content::get('identity')[$field];
}

it('stores an uploaded logo on the public disk', function (): void {
    $path = uploadIdentityImage($this->admin, 'logo_image');

    expect($path)->toStartWith('uploads/');
    Storage::disk('public')->assertExists($path);
});

it('shows the uploaded header image on the page', function (): void {
    $path = uploadIdentityImage($this->admin, 'photo');

    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::image($path), escape: false);
});

it('falls back to the text logo when no image is set', function (string $locale): void {
    // The navbar link must never render empty. The logo is itself translated,
    // so each locale is checked against its own text.
    app()->setLocale($locale);

    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee(Content::get('identity')['logo']);
})->with(['ar', 'en']);

it('renders the hero without an image when none is uploaded', function (): void {
    // Certificates legitimately use /storage/uploads, so this checks the hero
    // specifically: with no photo set, the two-column split never appears.
    expect(Content::get('identity')['photo'] ?? '')->toBe('');

    $this->get('/en')
        ->assertOk()
        ->assertDontSee('hero-split', escape: false);
});

it('deletes the previous file when an image is replaced', function (): void {
    $first = uploadIdentityImage($this->admin, 'photo');
    $second = uploadIdentityImage($this->admin, 'photo');

    expect($second)->not->toBe($first);

    // The replaced file must not linger on disk.
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

it('clears the stored path and lets the site fall back when an image is removed', function (): void {
    $path = uploadIdentityImage($this->admin, 'logo_image');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->call('removeImage', 0, 'logo_image')
        ->call('save')
        ->assertHasNoErrors();

    expect(Content::get('identity')['logo_image'])->toBe('');

    $this->get('/en')->assertOk()->assertDontSee($path, escape: false);
});

it('rejects a file that is not an image', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.photo', UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'))
        ->assertHasErrors('uploads.0.photo');
});

it('rejects an image over the size limit', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.photo', UploadedFile::fake()->image('huge.png')->size(4096))
        ->assertHasErrors('uploads.0.photo');
});

it('writes no file when the save fails validation', function (): void {
    // A required text field left blank must not leave an orphan on disk.
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.photo', UploadedFile::fake()->image('pic.png'))
        ->set('entries.0.name.en', '')
        ->call('save')
        ->assertHasErrors('entries.0.name.en');

    expect(Storage::disk('public')->files('uploads'))->toBeEmpty();
});

it('refuses to delete a path outside the uploads directory', function (): void {
    // A stored value pointing somewhere else must never cause a delete when the
    // image is replaced.
    Storage::disk('public')->put('important/keep.png', 'x');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.photo', 'important/keep.png')
        ->set('uploads.0.photo', UploadedFile::fake()->image('new.png'))
        ->call('save')
        ->assertHasNoErrors();

    Storage::disk('public')->assertExists('important/keep.png');
});

/*
| Theme-paired logos
*/

it('falls back to the text logo when only a dark logo is uploaded', function (): void {
    // A dark logo alone is not enough: without a light one the navbar would be
    // empty in light mode, so the text logo has to stand in.
    uploadIdentityImage($this->admin, 'logo_image_dark');

    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::get('identity')['logo']);
});

it('defines a CSS rule for each theme logo', function (): void {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('.logo-dark')
        ->and($css)->toContain('.dark .logo-light')
        ->and($css)->toContain('.dark .logo-dark');
});

it('deletes only the replaced logo, leaving its theme pair alone', function (): void {
    $light = uploadIdentityImage($this->admin, 'logo_image');
    $dark = uploadIdentityImage($this->admin, 'logo_image_dark');

    $newLight = uploadIdentityImage($this->admin, 'logo_image');

    Storage::disk('public')->assertMissing($light);
    Storage::disk('public')->assertExists($newLight);
    // Replacing one theme's logo must not disturb the other's.
    Storage::disk('public')->assertExists($dark);
});

/*
| Certificates
*/

it('renders the lightbox dialog once per page', function (): void {
    $html = (string) $this->get('/en')->assertOk()->getContent();

    expect(substr_count($html, 'role="dialog"'))->toBe(1);
});
