<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('sorts previous jobs newest first and shows only the start year', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'previous_jobs'])
        ->set('entries', [
            ['company' => 'Older', 'role' => ['ar' => 'مطوّر', 'en' => 'Developer'], 'url' => '', 'work_mode' => '', 'start' => '2020-08', 'end' => '2023-03'],
            ['company' => 'Undated', 'role' => ['ar' => 'مطوّر', 'en' => 'Developer'], 'url' => '', 'work_mode' => '', 'start' => '', 'end' => ''],
            ['company' => 'Newer', 'role' => ['ar' => 'مهندس', 'en' => 'Engineer'], 'url' => '', 'work_mode' => '', 'start' => '2023-09', 'end' => '2025-05'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()
        ->assertSeeInOrder(['Undated', 'Newer', '· 2023', 'Older', '· 2020', 'Alumnus of'])
        ->assertDontSee('2023-09')
        ->assertDontSee('2025');

    $this->get('/ar')->assertOk()->assertSee('· ٢٠٢٣');
});

it('keeps the only dated job at the bottom when it is the oldest', function (): void {
    // Dashboard order puts the dated (oldest) job first, as in a real list
    // where only the internship got its date filled in.
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'previous_jobs'])
        ->set('entries', [
            ['company' => 'Intern Co', 'role' => ['ar' => 'متدرّب', 'en' => 'Intern'], 'url' => '', 'work_mode' => '', 'start' => '2018-12', 'end' => '2019-04'],
            ['company' => 'Latest Co', 'role' => ['ar' => 'مهندس', 'en' => 'Engineer'], 'url' => '', 'work_mode' => '', 'start' => '', 'end' => ''],
            ['company' => 'Middle Co', 'role' => ['ar' => 'مطوّر', 'en' => 'Developer'], 'url' => '', 'work_mode' => '', 'start' => '', 'end' => ''],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertSeeInOrder(['Latest Co', 'Middle Co', 'Intern Co', '· 2018', 'Alumnus of']);
});

it('keeps the M2GATE line after the jobs, set apart, with its year', function (): void {
    $this->get('/en')->assertOk()->assertSeeInOrder(['intro__previous', 'class="intro__alumni"', 'M²GATE', '· 2018'], escape: false);
});

it('rejects a job date that is not a month', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'previous_jobs'])
        ->set('entries.0.start', '2023-13')
        ->call('save')
        ->assertHasErrors('entries.0.start');
});
