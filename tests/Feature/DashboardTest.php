<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

/*
| Access
*/

it('sends a guest to the login page', function (string $path): void {
    $this->get($path)->assertRedirect(route('admin.login'));
})->with(['/admin', '/admin/projects', '/admin/identity']);

it('lets a signed-in admin reach the dashboard', function (): void {
    $this->actingAs($this->admin)->get('/admin')->assertOk();
});

it('serves an editor for every configured section', function (): void {
    foreach (array_keys(config('dashboard.sections')) as $section) {
        $this->actingAs($this->admin)->get("/admin/{$section}")->assertOk();
    }
});

it('404s on a section that is not in the schema', function (): void {
    $this->actingAs($this->admin)->get('/admin/not-a-section')->assertNotFound();
});

it('signs a user in and back out', function (): void {
    $user = User::factory()->create(['password' => 'correct-horse-battery']);

    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'correct-horse-battery',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();

    $this->post('/admin/logout')->assertRedirect(route('admin.login'));
    $this->assertGuest();
});

it('rejects a wrong password without signing in', function (): void {
    $user = User::factory()->create();

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('keeps the dashboard out of search results', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertSee('name="robots" content="noindex, nofollow"', escape: false);
});

/*
| Editing
*/

it('expands a list entry when opened', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'projects'])
        ->assertSet('open', null)
        ->set('open', 0)
        ->assertSee('wire:model="entries.0.slug"', escape: false);
});

it('opens a singleton straight away, with no list to browse', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->assertSet('open', 0)
        ->assertSee('wire:model="entries.0.email"', escape: false);
});

it('saves an edit and shows it on the site in both locales', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.name.en', 'Edited Name')
        ->set('entries.0.name.ar', 'اسم معدل')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertSee('Edited Name');
    $this->get('/ar')->assertSee('اسم معدل');
});

it('requires both locales on a translated field', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.name.en', '')
        ->call('save')
        ->assertHasErrors('entries.0.name.en');

    // The half-filled edit must not have reached the database.
    expect(Content::string('identity.name'))->not->toBe('');
});

it('adds, reorders and removes list entries', function (): void {
    $component = Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'certificates']);

    $original = ContentSection::query()->section('certificates')->count();

    // Add one, and it lands at the end.
    $component->call('addEntry')
        ->set('entries.'.$original.'.name.en', 'New Certificate')
        ->set('entries.'.$original.'.name.ar', 'شهادة جديدة')
        ->set('entries.'.$original.'.issuer.en', 'Issuer')
        ->set('entries.'.$original.'.issuer.ar', 'جهة')
        ->set('entries.'.$original.'.date.en', '2026')
        ->set('entries.'.$original.'.date.ar', '٢٠٢٦')
        ->call('save')
        ->assertHasNoErrors();

    expect(ContentSection::query()->section('certificates')->count())->toBe($original + 1);

    // Move it to the top and confirm the stored order changed with it.
    for ($i = $original; $i > 0; $i--) {
        $component->call('move', $i, -1);
    }

    $component->call('save')->assertHasNoErrors();

    $first = ContentSection::query()->section('certificates')->first();
    expect($first->data['name']['en'])->toBe('New Certificate');

    // Remove it again.
    $component->call('removeEntry', 0)->call('save')->assertHasNoErrors();

    expect(ContentSection::query()->section('certificates')->count())->toBe($original);
});

it('drops repeatable rows left blank rather than shipping empty bullets', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'projects'])
        ->call('addItem', 0, 'stack')
        ->call('addItem', 0, 'highlights')
        ->call('save')
        ->assertHasNoErrors();

    $project = ContentSection::query()->section('projects')->first();

    expect($project->data['stack'])->not->toContain('')
        ->and($project->data['highlights'])->each->not->toBe(['ar' => '', 'en' => '']);
});

it('reflects a save immediately, without a stale cached tree', function (): void {
    // The site reads a cached tree, so a save that does not flush it would
    // leave the dashboard showing new content and the site showing old.
    $this->get('/en')->assertDontSee('Freshly Cached Name');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.name.en', 'Freshly Cached Name')
        ->set('entries.0.name.ar', 'اسم محدث')
        ->call('save');

    $this->get('/en')->assertSee('Freshly Cached Name');
});

it('refuses to edit a section outside the schema', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'made_up'])
        ->assertStatus(404);
});

it('never deletes a singleton section by saving it empty', function (): void {
    // save() replaces the section wholesale. With no entries that means a
    // delete followed by no insert — the About text vanished this way, taking
    // the whole page down with an undefined-key error.
    $before = Content::get('about');

    expect($before['intro'])->not->toBe('');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'about'])
        ->set('entries', [])
        ->call('save');

    expect(ContentSection::query()->section('about')->count())->toBe(1)
        ->and(Content::get('about')['intro'])->toBe($before['intro']);

    // And the page still renders.
    $this->get('/en')->assertOk();
});

it('saves every section without crashing', function (string $section): void {
    // Livewire 4 resolves `rules` as a magic property, so a private rules()
    // method is unreachable from validate() and save() throws
    // MissingRulesException. Saving each section is the only way to catch it.
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => $section])
        ->call('save')
        ->assertHasNoErrors();
})->with([
    'identity', 'hero', 'projects', 'other_projects', 'timeline', 'freelance',
    'side_projects', 'education', 'certificates', 'skills', 'channel',
    'writing', 'what_i_build', 'about', 'contact', 'socials',
]);
