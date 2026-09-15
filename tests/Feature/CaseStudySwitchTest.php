<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;

// Case studies are behind a dashboard switch, so half-written studies can sit
// in the database without being reachable.

function enableCaseStudies(bool $on): void
{
    setSetting('case_studies_enabled', $on);
}

/**
 * Give a project a written case study.
 *
 * Seeded content comes from config/portfolio.php, which carries no case-study
 * text — that is written from the dashboard — so a test that needs one writes
 * it. (ProjectPageTest::fillCaseStudy does the same for its own file.)
 */
function writeCaseStudy(string $slug = 'boxesvdr'): void
{
    $row = ContentSection::query()
        ->where('section', 'projects')
        ->get()
        ->first(fn (ContentSection $r): bool => ($r->data['slug'] ?? null) === $slug);

    $row->update(['data' => [
        ...$row->data,
        'context' => ['ar' => 'سياق المشروع.', 'en' => 'The background to this project.'],
    ]]);

    Content::flush();
}

it('hides the case-study link when the switch is off', function (): void {
    enableCaseStudies(false);

    $this->get('/en')
        ->assertOk()
        ->assertDontSee('Read the case study');
});

it('404s a project page when the switch is off', function (): void {
    enableCaseStudies(false);

    // BoxesVDR is the one project with written sections.
    $this->get('/en/projects/boxesvdr')->assertNotFound();
});

it('shows the link and serves the page when the switch is on', function (): void {
    writeCaseStudy();
    enableCaseStudies(true);

    $this->get('/en')
        ->assertOk()
        ->assertSee('Read the case study');

    $this->get('/en/projects/boxesvdr')->assertOk();
});

it('keeps case-study text while the switch is off', function (): void {
    writeCaseStudy();
    enableCaseStudies(false);

    // Switching off must hide, never delete.
    $project = collect(Content::list('projects'))
        ->firstWhere('slug', 'boxesvdr');

    expect(trim((string) ($project['context'] ?? '')))->not->toBe('');

    enableCaseStudies(true);

    $this->get('/en/projects/boxesvdr')->assertOk();
});

it('keeps project pages out of the sitemap when the switch is off', function (): void {
    enableCaseStudies(false);

    // Advertising a URL that 404s is worse than omitting it.
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSee('/projects/boxesvdr');
});

it('lists project pages in the sitemap when the switch is on', function (): void {
    writeCaseStudy();
    enableCaseStudies(true);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('/projects/boxesvdr');
});

it('links out to a project with a url', function (): void {
    $this->get('/en')
        ->assertOk()
        ->assertSee('Visit project')
        ->assertSee('https://boxesvdr.com', escape: false);
});
