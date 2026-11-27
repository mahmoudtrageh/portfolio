<?php

declare(strict_types=1);

use App\Models\ContentSection;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProjectsSeeder;

it('adds Longimanus as a freelance project and Larabuilder as a previous-job project', function (): void {
    $this->seed(ProjectsSeeder::class);

    $this->get('/en/projects')->assertOk()
        ->assertSeeInOrder(['At previous jobs', 'Larabuilder', 'As a freelancer', 'Longimanus Liveaboard']);
});

it('appends each project once, after the ones already in the dashboard', function (): void {
    $before = ContentSection::query()->section('freelance_projects')->max('position');

    $this->seed(ProjectsSeeder::class);
    $this->seed(ProjectsSeeder::class);

    $rows = ContentSection::query()->section('freelance_projects')->get()
        ->filter(fn (ContentSection $row): bool => ($row->data['name']['en'] ?? '') === 'Longimanus Liveaboard');

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->position)->toBe($before === null ? 0 : $before + 1)
        ->and(ContentSection::query()->section('job_projects')->get()
            ->filter(fn (ContentSection $row): bool => ($row->data['name']['en'] ?? '') === 'Larabuilder'))->toHaveCount(1);
});

it('runs from the database seeder', function (): void {
    $this->seed(DatabaseSeeder::class);

    $names = ContentSection::query()->whereIn('section', ['freelance_projects', 'job_projects'])->get()
        ->map(fn (ContentSection $row): string => (string) ($row->data['name']['en'] ?? ''));

    expect($names)->toContain('Longimanus Liveaboard', 'Larabuilder');
});

it('never replaces existing content when the database seeder runs', function (): void {
    $row = ContentSection::query()->create([
        'section' => 'job_projects',
        'position' => 99,
        'data' => ['name' => ['ar' => 'معدّل', 'en' => 'Edited in production'], 'about' => ['ar' => '', 'en' => ''], 'url' => ''],
    ]);
    $count = ContentSection::query()->count();

    $this->seed(DatabaseSeeder::class);

    expect(ContentSection::query()->find($row->id)?->data['name']['en'])->toBe('Edited in production')
        ->and(ContentSection::query()->count())->toBeGreaterThanOrEqual($count);
});

it('loads the full content into an empty database', function (): void {
    ContentSection::query()->delete();

    $this->seed(DatabaseSeeder::class);

    expect(ContentSection::query()->section('identity')->exists())->toBeTrue();
});
