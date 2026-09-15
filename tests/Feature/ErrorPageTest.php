<?php

declare(strict_types=1);
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('renders a styled 404 for an unknown path outside any locale', function (): void {
    // No locale segment at all — the URL defaults are never bound, so this is
    // the case most likely to blow up inside the shared layout.
    $this->withoutExceptionHandling(except: [
        NotFoundHttpException::class,
    ]);

    $this->get('/no-such-page')
        ->assertNotFound()
        ->assertSee('404');
});

it('renders a localised 404 for an unknown path inside a locale', function (string $locale, string $expected): void {
    $this->get("/{$locale}/nope")
        ->assertNotFound()
        ->assertSee($expected);
})->with([
    ['ar', 'الصفحة غير موجودة'],
    ['en', 'Page not found'],
]);
