<?php

declare(strict_types=1);

use App\Support\Content;

it('redirects the bare root to the default locale', function (): void {
    $this->get('/')->assertRedirect('/'.config('app.locale'));
});

it('serves the page in every locale', function (string $locale): void {
    $this->get("/{$locale}")->assertOk();
})->with(['ar', 'en']);

it('serves home, projects and contact as separate pages', function (string $locale, string $path): void {
    $this->get("/{$locale}{$path}")->assertOk();
})->with(['ar', 'en'])->with(['', '/projects', '/contact']);

it('lists every project on the projects page', function (string $locale): void {
    $response = $this->get("/{$locale}/projects")->assertOk();

    foreach (['Boxes VDR', 'Linkatik', 'ASP Paris', 'PetroApp', 'Vowalaa', 'MatX', 'DSync Solutions'] as $name) {
        $response->assertSee($name);
    }
})->with(['ar', 'en']);

it('shows the email and phone on the contact page', function (): void {
    $this->get('/en/contact')
        ->assertOk()
        ->assertSee('mailto:', escape: false)
        ->assertSee('tel:', escape: false);
});

it('marks the current page in the navigation', function (): void {
    $this->get('/en/projects')
        ->assertOk()
        ->assertSee('href="'.route('projects', ['locale' => 'en']).'"', escape: false)
        ->assertSee('aria-current="page"', escape: false);
});

it('no longer serves the old multi-page URLs', function (string $path): void {
    $this->get($path)->assertNotFound();
})->with(['/ar/about', '/en/testimonials']);

it('404s on an unsupported locale', function (): void {
    $this->get('/fr')->assertNotFound();
});

it('sets dir=rtl and lang=ar on the Arabic page', function (): void {
    $this->get('/ar')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl">', escape: false);
});

it('sets dir=ltr and lang=en on the English page', function (): void {
    $this->get('/en')
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr">', escape: false);
});

it('renders localised content per locale', function (): void {
    $this->get('/ar')
        ->assertSee('محمود طه')
        ->assertDontSee('A backend engineer with 6+ years');

    $this->get('/en')
        ->assertSee('Mahmoud Taha')
        ->assertDontSee('مهندس Backend بخبرة');
});

it('uses Arabic-Indic digits in Arabic and Western digits in English', function (): void {
    // Numerals are localised too — a shared numeral would read wrong in one
    // of the two columns.
    $ar = (string) $this->get('/ar')->getContent();
    $en = (string) $this->get('/en')->getContent();

    expect($ar)->toMatch('/[٠-٩]/u');
    expect($en)->not->toMatch('/[٠-٩]/u');
});

it('emits hreflang alternates for both locales', function (): void {
    $this->get('/ar')
        ->assertSee('hreflang="ar"', escape: false)
        ->assertSee('hreflang="en"', escape: false)
        ->assertSee('hreflang="x-default"', escape: false);
});

it('points the language switcher at the same page in the other locale', function (): void {
    $this->get('/ar')->assertSee('href="/en"', escape: false);
    $this->get('/en')->assertSee('href="/ar"', escape: false);
});

it('resolves translatable content trees to the active locale', function (): void {
    app()->setLocale('ar');
    expect(Content::string('identity.name'))->toBe('محمود طه');
    expect(Content::dir())->toBe('rtl');
    expect(Content::alternateLocale())->toBe('en');

    app()->setLocale('en');
    expect(Content::string('identity.name'))->toBe('Mahmoud Taha');
    expect(Content::dir())->toBe('ltr');
    expect(Content::alternateLocale())->toBe('ar');
});

it('leaves plain lists untouched while localising', function (): void {
    app()->setLocale('en');

    // 'stack' is a flat list of tech names, not a translatable pair.
    $projects = Content::list('projects');
    $project = collect($projects)->firstWhere('slug', 'boxesvdr');

    expect($project['stack'])->toContain('Laravel')
        ->and($project['name'])->toBe('BoxesVDR');
});

it('exposes a sitemap covering the page in every locale', function (): void {
    $response = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    foreach (['ar', 'en'] as $locale) {
        $response->assertSee(url("/{$locale}"), escape: false);
    }
});

it('never shows a Latin-digit date or figure in Arabic body text', function (): void {
    // The Arabic column uses Arabic-Indic numerals for its own figures, so a
    // Latin year or quantity in visible text means a value was left out of the
    // translatable tree — the bug that put "2026" on the Arabic project cards.
    //
    // Proper nouns that simply contain a digit (PM2, ArgoCD) are names, not
    // figures, and stay Latin in both locales; so does the dialling number.
    $html = (string) $this->get('/ar')->assertOk()->getContent();

    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);

    $xpath = new DOMXPath($document);

    // Visible prose only: no markup, no script/style payloads.
    $text = '';

    foreach ($xpath->query('//text()[not(ancestor::script or ancestor::style)]') as $node) {
        $text .= ' '.$node->textContent;
    }

    $text = str_replace(config('portfolio.identity.phone'), ' ', $text);

    // A standalone run of Latin digits — one not welded into a name like PM2.
    // The class is spelled out as 0-9 rather than \d, which under /u also
    // matches the Arabic-Indic digits this page is supposed to be full of.
    preg_match_all('/(?<![\p{L}\p{N}])[0-9]+(?![\p{L}\p{N}])/u', $text, $matches);

    expect($matches[0])->toBeEmpty(
        'Latin-digit figures leaked into the Arabic page: '.implode(', ', $matches[0])
    );
});
