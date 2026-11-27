<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use App\Support\Profile;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('leaves only the project count on the photo while birthdate, location and career start are empty', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.birthdate', '')
        ->set('entries.0.location', ['ar' => '', 'en' => ''])
        ->set('entries.0.career_start', '')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()
        ->assertSee('<figcaption class="intro__caption"', escape: false)
        ->assertSee(Profile::projectCount().' projects')
        ->assertDontSee('years old')
        ->assertDontSee('yrs experience');
});

it('saves birthdate and location and shows them on the home photo', function (): void {
    Carbon::setTestNow('2026-11-22');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.birthdate', '1996-12-01')
        ->set('entries.0.location', ['ar' => 'القاهرة، مصر', 'en' => 'Cairo, Egypt'])
        ->call('save')
        ->assertHasNoErrors();

    expect(Profile::age())->toBe(29);

    $this->get('/en')->assertOk()->assertSee('Cairo, Egypt')->assertSee('29 years old')->assertDontSee('1996-12-01');
    $this->get('/ar')->assertOk()->assertSee('القاهرة، مصر')->assertSee('29 عامًا');
});

it('rejects a birthdate that is not a past date', function (string $value): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.birthdate', $value)
        ->call('save')
        ->assertHasErrors('entries.0.birthdate');
})->with(['next year' => '2099-01-01', 'garbage' => 'not a date']);

it('works out years of experience from the career start and shows them on the home photo', function (): void {
    Carbon::setTestNow('2026-11-22');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.birthdate', '1996-12-01')
        ->set('entries.0.career_start', '2020-08-01')
        ->call('save')
        ->assertHasNoErrors();

    expect(Profile::experience())->toBe(6);

    $this->get('/en')->assertOk()
        ->assertSeeInOrder(['29 years old', '6 yrs experience'])
        ->assertDontSee('2020-08-01');
    $this->get('/ar')->assertOk()->assertSee('خبرة 6 سنوات');
});

it('shows experience alone on the photo when nothing else is set', function (): void {
    Carbon::setTestNow('2026-11-22');

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.birthdate', '')
        ->set('entries.0.location', ['ar' => '', 'en' => ''])
        ->set('entries.0.career_start', '2020-08-01')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()
        ->assertSee('<figcaption class="intro__caption">', escape: false)
        ->assertSee('6 yrs experience');
});

it('rejects a career start that is not a past date', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.career_start', '2099-01-01')
        ->call('save')
        ->assertHasErrors('entries.0.career_start');
});

it('shows the work mode in small text beside a previous job', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'previous_jobs'])
        ->set('entries', [
            ['company' => 'Tazamun', 'role' => ['ar' => 'مهندس', 'en' => 'Engineer'], 'url' => '', 'work_mode' => 'remote'],
            ['company' => 'Algoriza', 'role' => ['ar' => 'مطوّر', 'en' => 'Developer'], 'url' => '', 'work_mode' => 'onsite'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()
        ->assertSee('<span class="intro__mode">(Remote)</span>', escape: false)
        ->assertSee('<span class="intro__mode">(On-site)</span>', escape: false);

    $this->get('/ar')->assertOk()->assertSee('(عن بُعد)')->assertSee('(من المقر)');
});

it('rejects an unknown work mode', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'previous_jobs'])
        ->set('entries.0.work_mode', 'moon')
        ->call('save')
        ->assertHasErrors('entries.0.work_mode');
});

it('shows the work mode beside the current job', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'current_job'])
        ->set('entries.0.company', 'Boxes')
        ->set('entries.0.work_mode', 'remote')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSee('<span class="intro__mode">(Remote)</span>', escape: false);
});

it('shows the tech stack as tags after the jobs, edited from the dashboard', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'tech_stack'])
        ->set('entries.0.items', ['Laravel'])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSee('<li class="intro__tag">Laravel</li>', escape: false);

    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'tech_stack'])
        ->set('entries.0.items', ['Go', '', 'Kubernetes'])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()
        ->assertSeeInOrder(['<li class="intro__tag">Go</li>', '<li class="intro__tag">Kubernetes</li>'], escape: false)
        ->assertDontSee('<li class="intro__tag">Laravel</li>', escape: false);
});

it('hides the tech tags once the list is emptied', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'tech_stack'])
        ->set('entries.0.items', [])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertDontSee('<ul class="intro__tags"', escape: false);
});

it('shows no tech tags while the tech stack was never set', function (): void {
    ContentSection::query()->section('tech_stack')->delete();
    Content::flush();

    expect(Profile::techStack())->toBe([]);

    $this->get('/en')->assertOk()->assertDontSee('<ul class="intro__tags"', escape: false);
});
