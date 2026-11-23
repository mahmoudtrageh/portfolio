<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('sorts previous jobs newest first without showing the dates', function (): void {
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
        ->assertSeeInOrder(['Newer', 'Older', 'Undated', 'Alumnus of'])
        ->assertDontSee('2023-09')
        ->assertDontSee('Sep 2023');
});

it('keeps the M2GATE line after the jobs, set apart', function (): void {
    $this->get('/en')->assertOk()->assertSeeInOrder(['intro__previous', 'class="intro__alumni"', 'M²GATE'], escape: false);
});

it('rejects a job date that is not a month', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'previous_jobs'])
        ->set('entries.0.start', '2023-13')
        ->call('save')
        ->assertHasErrors('entries.0.start');
});
