<?php

declare(strict_types=1);

use App\Http\Controllers\ProjectController;
use App\Livewire\Admin\SectionEditor;
use App\Models\ContentSection;
use App\Models\User;
use App\Support\Content;
use Livewire\Livewire;

/**
 * Give a project enough case-study content to earn a page.
 */
function fillCaseStudy(string $slug = 'boxesvdr'): ContentSection
{
    $row = ContentSection::query()->section('projects')->get()
        ->first(fn (ContentSection $r): bool => ($r->data['slug'] ?? '') === $slug);

    $data = $row->data;
    $data['context'] = ['ar' => '## سياق\n\nنص السياق.', 'en' => "## Context\n\nThe context body."];
    $data['challenges'] = ['ar' => 'التحدي الأول.', 'en' => 'The first challenge.'];
    $data['solution'] = ['ar' => 'الحل.', 'en' => 'The solution.'];
    $data['results'] = ['ar' => 'النتائج.', 'en' => 'The results.'];
    $data['lessons'] = ['ar' => 'الدروس.', 'en' => 'The lessons.'];
    $row->update(['data' => $data]);

    Content::flush();

    return $row->fresh();
}

beforeEach(function (): void {
    $this->admin = User::factory()->create();

    // Case-study pages ship switched off (Settings → Enable case-study pages).
    // Every test here is about those pages existing, so they turn it on;
    // CaseStudySwitchTest covers the off state.
    setSetting('case_studies_enabled', true);
});

it('serves a project page in each locale', function (string $locale): void {
    fillCaseStudy();

    app()->setLocale($locale);

    $this->get("/{$locale}/projects/boxesvdr")
        ->assertOk()
        ->assertSee('BoxesVDR');
})->with(['ar', 'en']);

it('renders every filled case-study section', function (): void {
    fillCaseStudy();

    $this->get('/en/projects/boxesvdr')
        ->assertOk()
        ->assertSee(__('Background & context'))
        ->assertSee(__('Challenges'))
        ->assertSee(__('Solution & implementation'))
        ->assertSee(__('Results & impact'))
        ->assertSee(__('Lessons learned'))
        ->assertSee('The context body.');
});

it('renders the sections from Markdown', function (): void {
    fillCaseStudy();

    $this->get('/en/projects/boxesvdr')
        ->assertOk()
        ->assertSee('<h2>Context</h2>', escape: false);
});

it('escapes raw HTML in a case-study section', function (): void {
    $row = fillCaseStudy();
    $data = $row->data;
    $data['solution'] = ['ar' => 'x', 'en' => 'Hello <script>alert(1)</script>'];
    $row->update(['data' => $data]);
    Content::flush();

    $this->get('/en/projects/boxesvdr')
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', escape: false);
});

it('hides a section that was left empty', function (): void {
    $row = fillCaseStudy();
    $data = $row->data;
    $data['lessons'] = ['ar' => '', 'en' => ''];
    $row->update(['data' => $data]);
    Content::flush();

    $this->get('/en/projects/boxesvdr')
        ->assertOk()
        ->assertDontSee(__('Lessons learned'));
});

it('404s for a project with no case study written', function (): void {
    // A page with a title over an empty body is worse than no page.
    $bare = collect(Content::list('projects'))
        ->first(fn (array $p): bool => ! ProjectController::hasCaseStudy($p));

    expect($bare)->not->toBeNull();

    $this->get('/en/projects/'.$bare['slug'])->assertNotFound();
});

it('404s on an unknown slug', function (): void {
    $this->get('/en/projects/no-such-project')->assertNotFound();
});

it('links the card to the page only when a case study exists', function (): void {
    fillCaseStudy();

    $html = (string) $this->get('/en')->assertOk()->getContent();

    expect($html)->toContain(route('project', ['locale' => 'en', 'slug' => 'boxesvdr']))
        ->and($html)->toContain(__('Read the case study'));
});

it('shows no case-study link on the card when there is no page', function (): void {
    // Nothing filled in anywhere, so no card should offer one.
    $this->get('/en')->assertOk()->assertDontSee(__('Read the case study'));
});

it('publishes valid JSON-LD for the project', function (): void {
    fillCaseStudy();

    $html = (string) $this->get('/en/projects/boxesvdr')->assertOk()->getContent();

    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    $json = json_decode(trim($m[1] ?? ''), true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE)
        ->and(collect($json)->pluck('@type')->all())
        ->toBe(['CreativeWork', 'BreadcrumbList']);
});

it('lists project pages in the sitemap, and omits ones without a case study', function (): void {
    fillCaseStudy();

    $bare = collect(Content::list('projects'))
        ->first(fn (array $p): bool => ! ProjectController::hasCaseStudy($p));

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('project', ['locale' => 'en', 'slug' => 'boxesvdr']), escape: false)
        ->assertDontSee('/projects/'.$bare['slug'], escape: false);
});

it('sets a canonical URL and article metadata', function (): void {
    fillCaseStudy();

    $url = route('project', ['locale' => 'en', 'slug' => 'boxesvdr']);

    $this->get('/en/projects/boxesvdr')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.$url.'">', escape: false)
        ->assertSee('<meta property="og:type" content="article">', escape: false);
});

it('lets the dashboard write a case-study section', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SectionEditor::class, ['section' => 'projects'])
        ->set('entries.0.context.en', '## Written from the dashboard')
        ->set('entries.0.context.ar', '## مكتوب من لوحة التحكم')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/en/projects/boxesvdr')
        ->assertOk()
        ->assertSee('<h2>Written from the dashboard</h2>', escape: false);
});
