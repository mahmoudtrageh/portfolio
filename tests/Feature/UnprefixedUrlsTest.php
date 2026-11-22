<?php

declare(strict_types=1);

/*
| With one language published, public URLs drop the locale prefix.
*/

beforeEach(function (): void {
    setSetting('multilingual', false);
    setSetting('primary_locale', 'en');
});

it('serves pages at bare paths', function (string $path): void {
    $this->get($path)->assertOk()->assertSee('lang="en"', escape: false);
})->with(['/', '/projects', '/contact', '/blog']);

it('redirects old prefixed links permanently, keeping the query string', function (): void {
    $this->get('/en')->assertStatus(301)->assertRedirect('/');
    $this->get('/en/projects')->assertStatus(301)->assertRedirect('/projects');
    $this->get('/en/blog?page=2')->assertRedirect('/blog?page=2');
    $this->get('/ar/contact')->assertRedirect('/contact');
});

it('links without the prefix', function (): void {
    $base = rtrim((string) config('app.url'), '/');

    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.$base.'/projects"', escape: false)
        ->assertSee('<link rel="canonical" href="'.$base.'">', escape: false)
        ->assertDontSee('href="'.$base.'/en', escape: false);
});

it('routes a form post at the bare path', function (): void {
    // Reaches the contact controller (and its validation), not a 404/405.
    $this->post('/contact', [])->assertSessionHasErrors();
});

it('404s an unknown bare path', function (): void {
    $this->get('/projects/does-not-exist')->assertNotFound();
});

it('leaves the dashboard alone', function (): void {
    $this->get('/admin/login')->assertOk();
});

it('keeps the prefix while multilingual is on', function (): void {
    setSetting('multilingual', true);

    $this->get('/')->assertRedirect('/'.config('app.locale'));
    $this->get('/en/projects')->assertOk()->assertSee('/en/projects', escape: false);
});
