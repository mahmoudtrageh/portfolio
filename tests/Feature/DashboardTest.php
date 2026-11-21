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
        ->test(SectionEditor::class, ['section' => 'socials'])
        ->assertSet('open', null)
        ->set('open', 0)
        ->assertSee('wire:model="entries.0.url"', escape: false);
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
        ->test(SectionEditor::class, ['section' => 'freelance_projects']);

    // Two entries to start from, so moving one has somewhere to go.
    foreach (['First', 'Second'] as $i => $name) {
        $component->call('addEntry')
            ->set("entries.{$i}.name.en", $name)
            ->set("entries.{$i}.name.ar", $name)
            ->set("entries.{$i}.about.en", 'About')
            ->set("entries.{$i}.about.ar", 'نبذة');
    }

    $component->call('save')->assertHasNoErrors();

    $original = ContentSection::query()->section('freelance_projects')->count();

    // Add one, and it lands at the end.
    $component->call('addEntry')
        ->set('entries.'.$original.'.name.en', 'New Project')
        ->set('entries.'.$original.'.name.ar', 'مشروع جديد')
        ->set('entries.'.$original.'.about.en', 'About')
        ->set('entries.'.$original.'.about.ar', 'نبذة')
        ->call('save')
        ->assertHasNoErrors();

    expect(ContentSection::query()->section('freelance_projects')->count())->toBe($original + 1);

    // Move it to the top and confirm the stored order changed with it.
    for ($i = $original; $i > 0; $i--) {
        $component->call('move', $i, -1);
    }

    $component->call('save')->assertHasNoErrors();

    $first = ContentSection::query()->section('freelance_projects')->orderBy('position')->first();
    expect($first->data['name']['en'])->toBe('New Project');

    // Remove it again.
    $component->call('removeEntry', 0)->call('save')->assertHasNoErrors();

    expect(ContentSection::query()->section('freelance_projects')->count())->toBe($original);
});

it('drops repeatable rows left blank rather than shipping empty bullets', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'current_job'])
        ->set('entries.0.company', 'Company')
        ->call('addItem', 0, 'products')
        ->call('addItem', 0, 'products')
        ->call('save')
        ->assertHasNoErrors();

    $job = ContentSection::query()->section('current_job')->first();

    expect($job->data['products'])->toBe([]);
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
    $before = Content::get('identity');

    expect($before['name'])->not->toBe('');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries', [])
        ->call('save');

    expect(ContentSection::query()->section('identity')->count())->toBe(1)
        ->and(Content::get('identity')['name'])->toBe($before['name']);

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
    'identity', 'socials', 'current_job', 'previous_jobs', 'job_projects', 'freelance_projects',
]);

it('edits the projects page from the dashboard', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'job_projects'])
        ->call('addEntry')
        ->set('entries.0.name.en', 'Dashboard Project')
        ->set('entries.0.name.ar', 'مشروع')
        ->set('entries.0.about.en', 'Added from the dashboard')
        ->set('entries.0.about.ar', 'أضيف من لوحة التحكم')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en/projects')->assertOk()->assertSee('Dashboard Project');
});

it('shows the job title from the dashboard in the browser tab', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.role.en', 'Software Engineer')
        ->set('entries.0.role.ar', 'مهندس برمجيات')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertSee('— Software Engineer</title>', escape: false);
});
