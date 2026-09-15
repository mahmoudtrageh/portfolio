<?php

declare(strict_types=1);

/*
| Guards on the content tree itself. These keep the AR/EN halves in step and
| make sure unverified figures stay visibly marked rather than passing as fact.
*/

/**
 * Walk the content tree and collect every translatable node.
 *
 * @return array<string, array<string, string>>
 */
function translatableNodes(mixed $node, string $path = ''): array
{
    if (! is_array($node)) {
        return [];
    }

    $locales = config('portfolio.locales');

    if ($node !== [] && array_keys($node) === $locales) {
        return [$path => $node];
    }

    $found = [];

    foreach ($node as $key => $child) {
        $found += translatableNodes($child, $path === '' ? (string) $key : "{$path}.{$key}");
    }

    return $found;
}

it('has a non-empty Arabic and English value for every translatable field', function (): void {
    // Fields the dashboard marks optional are one-sided by design — a gloss
    // translating an Arabic title is only needed on the English side — so they
    // are exempt from the both-locales rule.
    $optional = [];

    foreach (config('dashboard.sections') as $section => $schema) {
        foreach ($schema['fields'] ?? [] as $key => $field) {
            if ($field['optional'] ?? false) {
                $optional[] = "{$section}.{$key}";
            }
        }
    }

    $missing = [];

    foreach (translatableNodes(config('portfolio')) as $path => $node) {
        // Strip the list index: 'writing.0.gloss' is the 'writing.gloss' field.
        $field = preg_replace('/\.\d+\./', '.', $path);

        if (in_array($field, $optional, true)) {
            continue;
        }

        foreach (config('portfolio.locales') as $locale) {
            if (trim((string) ($node[$locale] ?? '')) === '') {
                $missing[] = "{$path}.{$locale}";
            }
        }
    }

    expect($missing)->toBeEmpty('untranslated content: '.implode(', ', $missing));
});

it('gives every project the fields its card renders', function (): void {
    foreach (config('portfolio.projects') as $project) {
        expect($project)->toHaveKeys([
            'slug', 'name', 'category', 'year', 'tagline', 'metric', 'stack',
        ]);

        expect($project['stack'])->not->toBeEmpty("{$project['slug']} has no tech stack");
    }
});

it('keeps project slugs unique', function (): void {
    $slugs = array_column(config('portfolio.projects'), 'slug');

    expect($slugs)->toHaveCount(count(array_unique($slugs)));
});

it('never leaks a raw placeholder marker onto the rendered page', function (string $path): void {
    // Placeholders are wrapped in «guillemets» in the config. x-metric converts
    // them into a hatched highlight, so the raw marker must never reach the DOM
    // — and an unfilled value can never read as a verified fact.
    $html = (string) $this->get($path)->assertOk()->getContent();

    expect($html)->not->toContain('«')->and($html)->not->toContain('»');
})->with(['/ar', '/en']);

it('hides social links whose URL is still a placeholder', function (): void {
    // A placeholder URL would render as a broken link, so those entries are
    // filtered out of the contact section and footer entirely.
    $unverified = array_filter(
        config('portfolio.socials'),
        fn (array $s): bool => str_contains((string) $s['url'], '«')
    );

    $html = (string) $this->get('/ar')->assertOk()->getContent();

    foreach ($unverified as $social) {
        expect($html)->not->toContain(trim((string) $social['url'], '«»'));
    }
});
