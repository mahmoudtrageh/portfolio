<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\User;
use App\Support\Profile;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('hides the photo caption while birthdate and location are empty', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'identity'])
        ->set('entries.0.birthdate', '')
        ->set('entries.0.location', ['ar' => '', 'en' => ''])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en')->assertOk()->assertDontSee('<figcaption class="intro__caption"', escape: false);
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
