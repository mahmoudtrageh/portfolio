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

it('shows the uploaded logo in the navbar instead of the text logo', function (): void {
    $path = uploadIdentityImage($this->admin, 'logo_image');

    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::image($path), escape: false);
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

it('keeps a pending upload with its row when rows are reordered', function (): void {
    // certificates is a list section, so rows can move; the upload must follow.
    $component = Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'certificates']);

    $component->set('uploads.0.name', UploadedFile::fake()->image('a.png'));

    $component->call('move', 0, 1);

    // The file now belongs to what is row 1 after the swap.
    expect($component->get('uploads'))->toHaveKey(1)
        ->and($component->get('uploads'))->not->toHaveKey(0);
});

it('drops a pending upload when its row is deleted', function (): void {
    $component = Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'certificates']);

    $component->set('uploads.1.name', UploadedFile::fake()->image('b.png'));

    $component->call('removeEntry', 0);

    // Row 1 became row 0, so the upload has to shift down with it rather than
    // staying attached to a position that now holds a different entry.
    expect($component->get('uploads'))->toHaveKey(0)
        ->and($component->get('uploads'))->not->toHaveKey(1);
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

it('renders both logos so CSS can swap them with the theme', function (): void {
    $light = uploadIdentityImage($this->admin, 'logo_image');
    $dark = uploadIdentityImage($this->admin, 'logo_image_dark');

    // Both must reach the DOM: the theme is toggled client-side, so choosing
    // one in PHP would serve a cached page the wrong logo.
    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::image($light), escape: false)
        ->assertSee(Content::image($dark), escape: false)
        ->assertSee('logo-light', escape: false)
        ->assertSee('logo-dark', escape: false);
});

it('uses the light logo in both themes when no dark one is set', function (): void {
    $light = uploadIdentityImage($this->admin, 'logo_image');

    // With nothing to swap to, the single logo carries no theme class — so it
    // is never hidden by the dark-mode rule.
    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::image($light), escape: false)
        ->assertDontSee('logo-light', escape: false)
        ->assertDontSee('logo-dark', escape: false);
});

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

it('shows a thumbnail and lightbox trigger for a certificate with an image', function (): void {
    $component = Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'certificates']);

    $component->set('uploads.0.image', UploadedFile::fake()->image('cert.png', 800, 600))
        ->call('save')
        ->assertHasNoErrors();

    $path = Content::list('certificates')[0]['image'];

    $this->get('/en')
        ->assertOk()
        ->assertSee(Content::image($path), escape: false)
        ->assertSee('$dispatch(\'lightbox\'', escape: false);
});

it('lists a certificate without an image and shows no thumbnail for it', function (): void {
    // The CV certificates carry no scan; they must still render as rows, just
    // without a thumbnail button. Content resolves to the app's default locale,
    // so read the name in the locale actually being requested.
    app()->setLocale('en');

    $imageless = collect(Content::list('certificates'))
        ->first(fn (array $c): bool => trim((string) ($c['image'] ?? '')) === '');

    expect($imageless)->not->toBeNull('expected at least one certificate with no image');

    $html = (string) $this->get('/en')->assertOk()->getContent();

    // Names are HTML-escaped in the page ("&" becomes "&amp;"), so match the
    // escaped form rather than the raw content value.
    $needle = e($imageless['name']);

    expect($html)->toContain($needle);

    // No lightbox trigger inside that certificate's own row.
    $at = strpos($html, $needle);
    expect(substr($html, max(0, $at - 900), 900))->not->toContain('$dispatch(\'lightbox\'');
});

it('links a certificate to its verification URL', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'certificates'])
        ->set('entries.0.url', 'https://coursera.org/verify/TESTCODE')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')
        ->assertOk()
        ->assertSee('https://coursera.org/verify/TESTCODE', escape: false)
        ->assertSee(__('Verify'));
});

it('omits the verify link when no URL is set', function (): void {
    // Some seeded certificates do carry a verify link, so this checks the
    // absence per-certificate rather than across the whole page.
    app()->setLocale('en');

    $withoutUrl = collect(Content::list('certificates'))
        ->first(fn (array $c): bool => trim((string) ($c['url'] ?? '')) === '');

    expect($withoutUrl)->not->toBeNull('expected at least one certificate with no URL');

    $html = (string) $this->get('/en')->assertOk()->getContent();

    // Find that certificate's row and confirm no Verify link sits inside it.
    // The name is HTML-escaped in the page, so match the escaped form.
    $at = strpos($html, e($withoutUrl['name']));
    expect($at)->not->toBeFalse();

    $row = substr($html, $at, 600);
    expect($row)->not->toContain(__('Verify'));
});

it('puts the lightbox trigger inside an Alpine scope', function (): void {
    // Alpine only binds x-on: inside an x-data subtree, so a trigger outside
    // one is silently inert — clicks do nothing. That was the actual bug here,
    // and it is invisible without checking the nesting.
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'certificates'])
        ->set('uploads.0.image', UploadedFile::fake()->image('cert.png'))
        ->call('save')
        ->assertHasNoErrors();

    $html = (string) $this->get('/en')->assertOk()->getContent();

    $trigger = strpos($html, '$dispatch(\'lightbox\'');
    expect($trigger)->not->toBeFalse('no lightbox trigger rendered');

    // The nearest x-data before the trigger must be the certificates card,
    // not something far up the page that happens to precede it.
    $scope = strrpos(substr($html, 0, $trigger), 'x-data');
    expect($scope)->not->toBeFalse('trigger is not inside any Alpine scope');

    $between = substr($html, $scope, $trigger - $scope);
    expect($between)->toContain('Certificates');
});

it('renders the lightbox dialog once per page', function (): void {
    $html = (string) $this->get('/en')->assertOk()->getContent();

    expect(substr_count($html, 'role="dialog"'))->toBe(1);
});
