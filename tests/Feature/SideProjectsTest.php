<?php

declare(strict_types=1);

use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use App\Support\Profile;
use Database\Seeders\SideProjectsSeeder;
use Livewire\Livewire;

it('seeds the side projects and shows them as their own group on the Projects page', function (): void {
    ContentSection::query()->section('side_projects')->delete();

    $this->seed(SideProjectsSeeder::class);

    expect(ContentSection::query()->section('side_projects')->count())->toBe(4);

    $this->get('/en/projects')->assertOk()
        ->assertSeeInOrder(['As a freelancer', 'Side projects', 'Waazefa', 'Qotfa', 'Koomiz', 'BinaaSoft Shop'])
        ->assertSee('https://waazefa.com/', escape: false)
        ->assertSee('--groups: 4;', escape: false);
});

it('shows how many projects there are on the home photo, following the dashboard', function (): void {
    ContentSection::query()->section('side_projects')->delete();
    Content::flush();
    $before = Profile::projectCount();

    $this->seed(SideProjectsSeeder::class);

    expect(Profile::projectCount())->toBe($before + 4);

    $this->get('/en')->assertOk()->assertSee(($before + 4).' projects');
});

it('words the project count in each language', function (string $locale, int $count, string $expected): void {
    app()->setLocale($locale);

    expect(Profile::countLabel($count))->toBe($expected);
})->with([
    ['en', 0, ''],
    ['en', 1, '1 project'],
    ['en', 14, '14 projects'],
    ['ar', 1, 'مشروع واحد'],
    ['ar', 2, 'مشروعان'],
    ['ar', 7, '٧ مشاريع'],
    ['ar', 14, '١٤ مشروعًا'],
    ['ar', 103, '١٠٣ مشاريع'],
]);

it('never overwrites side projects already edited in the dashboard', function (): void {
    ContentSection::query()->section('side_projects')->delete();
    ContentSection::query()->create([
        'section' => 'side_projects',
        'position' => 0,
        'data' => ['name' => ['ar' => 'مشروعي', 'en' => 'My project'], 'about' => ['ar' => 'وصف', 'en' => 'About'], 'url' => ''],
    ]);

    $this->seed(SideProjectsSeeder::class);

    expect(ContentSection::query()->section('side_projects')->count())->toBe(1);
});

it('manages side projects from the dashboard, and hides the group once emptied', function (): void {
    $admin = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(SectionEditor::class, ['section' => 'side_projects'])
        ->set('entries', [
            ['name' => ['ar' => 'تجربة', 'en' => 'Trial App'], 'about' => ['ar' => 'وصف', 'en' => 'A test product'], 'url' => 'https://example.com'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en/projects')->assertOk()->assertSee('Trial App')->assertSee('A test product');

    Livewire::actingAs($admin)
        ->test(SectionEditor::class, ['section' => 'side_projects'])
        ->set('entries', [])
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en/projects')->assertOk()->assertDontSee('Side projects')->assertSee('--groups: 3;', escape: false);
});
