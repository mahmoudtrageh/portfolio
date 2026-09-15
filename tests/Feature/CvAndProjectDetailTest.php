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

/*
| CV upload
*/

it('hides the download button until a CV is uploaded', function (string $locale): void {
    expect(Content::get('identity')['cv'])->toBe('');

    $this->get("/{$locale}")
        ->assertOk()
        ->assertDontSee(__('Download CV', [], $locale));
})->with(['ar', 'en']);

it('stores an uploaded CV and shows the button in both languages', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.cv', UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'))
        ->call('save')
        ->assertHasNoErrors();

    $path = Content::get('identity')['cv'];

    expect($path)->toStartWith('uploads/');
    Storage::disk('public')->assertExists($path);

    // One English file, served in both languages.
    foreach (['ar', 'en'] as $locale) {
        $this->get("/{$locale}")
            ->assertOk()
            ->assertSee(Content::image($path), escape: false);
    }
});

it('rejects a CV that is not a PDF', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.cv', UploadedFile::fake()->image('not-a-cv.png'))
        ->assertHasErrors('uploads.0.cv');
});

it('deletes the previous CV when a new one replaces it', function (): void {
    $upload = fn () => Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.cv', UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasNoErrors();

    $upload();
    $first = Content::get('identity')['cv'];

    $upload();
    $second = Content::get('identity')['cv'];

    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

it('clears the CV and hides the button again', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('uploads.0.cv', UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'))
        ->call('save')
        ->call('removeImage', 0, 'cv')
        ->call('save')
        ->assertHasNoErrors();

    expect(Content::get('identity')['cv'])->toBe('');

    $this->get('/en')->assertOk()->assertDontSee(__('Download CV'));
});

/*
| Project detail
*/

it('keeps the card a teaser, with the detail on the project page', function (string $locale): void {
    // The card used to expand inline as well as link out. Two routes to the
    // same content is one too many, so the long-form detail now lives only on
    // /{locale}/projects/{slug}.
    app()->setLocale($locale);

    $project = collect(Content::list('projects'))
        ->first(fn (array $p): bool => trim((string) ($p['description'] ?? '')) !== '');

    expect($project)->not->toBeNull();

    $html = (string) $this->get("/{$locale}")->assertOk()->getContent();

    // The card shows the tagline and outcome, never the full description.
    expect($html)->toContain(e($project['tagline']))
        ->and($html)->not->toContain(e($project['description']))
        ->and($html)->not->toContain('project-detail-');
})->with(['ar', 'en']);
