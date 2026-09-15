<?php

declare(strict_types=1);

/*
| The plan (§13b) requires all four locale × theme combinations to work.
| Theme selection happens client-side, so these tests verify the server ships
| the machinery that makes it work: the pre-paint script, the toggle, and
| tokens defined for both themes.
*/

it('inlines the theme script before any stylesheet so the theme never flashes', function (
    string $locale
): void {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    $scriptAt = strpos($html, "localStorage.getItem('theme')");
    $cssAt = strpos($html, 'rel="stylesheet"');

    expect($scriptAt)->not->toBeFalse('the pre-paint theme script is missing');
    expect($cssAt)->not->toBeFalse('no stylesheet was linked');
    expect($scriptAt)->toBeLessThan($cssAt);
})->with(['ar', 'en']);

it('defaults to the system preference on a first visit', function (): void {
    $this->get('/ar')
        ->assertSee('prefers-color-scheme: dark', escape: false);
});

it('renders a theme toggle that persists the choice', function (string $locale): void {
    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee("localStorage.setItem('theme'", escape: false)
        ->assertSee('aria-label="'.__('Toggle theme', [], $locale).'"', escape: false);
})->with(['ar', 'en']);

it('defines colour tokens for both themes in the compiled stylesheet', function (): void {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain(':root')
        ->and($css)->toContain('.dark')
        // Dark mode must be a real palette, not an inversion filter.
        ->and($css)->not->toContain('filter: invert');

    // Every token declared on :root must also be declared for .dark.
    preg_match('/:root\s*\{(.*?)\}/s', $css, $light);
    preg_match('/\.dark\s*\{(.*?)\}/s', $css, $dark);

    preg_match_all('/(--[a-z-]+):/', $light[1], $lightTokens);
    preg_match_all('/(--[a-z-]+):/', $dark[1], $darkTokens);

    expect(array_diff($lightTokens[1], $darkTokens[1]))->toBeEmpty(
        'these tokens have no dark-mode value: '.implode(', ', array_diff($lightTokens[1], $darkTokens[1]))
    );
});

it('uses logical CSS properties so layouts flip with direction', function (): void {
    $css = file_get_contents(resource_path('css/app.css'));

    // Physical directional properties break RTL (plan §13a).
    expect($css)->not->toMatch('/[^-]margin-left:/')
        ->and($css)->not->toMatch('/[^-]margin-right:/')
        ->and($css)->not->toMatch('/[^-]padding-left:/')
        ->and($css)->not->toMatch('/[^-]padding-right:/');
});

it('keeps text contrast inside the comfortable band in both themes', function (): void {
    // Every pairing must clear its WCAG bar, but also stay under ~13:1 for body
    // text: near-black on near-white (about 15:1) is what makes long-form
    // reading feel harsh, and that is the regression this guards against.
    $css = file_get_contents(resource_path('css/app.css'));

    $tokensIn = function (string $pattern) use ($css): array {
        preg_match($pattern, $css, $m);

        preg_match_all('/(--[a-z-]+):\s*oklch\(([\d.]+)\s+([\d.]+)\s+([\d.]+)/', $m[1], $found, PREG_SET_ORDER);

        return array_reduce($found, function (array $carry, array $t): array {
            $carry[$t[1]] = [(float) $t[2], (float) $t[3], (float) $t[4]];

            return $carry;
        }, []);
    };

    $toSrgb = function (array $oklch): array {
        [$L, $C, $h] = $oklch;
        $h = deg2rad($h);
        $a = $C * cos($h);
        $b = $C * sin($h);

        $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

        return array_map(
            fn (float $v): float => max(0.0, min(1.0, $v)),
            [
                4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
                -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
                -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s,
            ],
        );
    };

    $ratio = function (array $x, array $y) use ($toSrgb): float {
        $lum = function (array $c): float {
            return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
        };

        $a = $lum($toSrgb($x));
        $b = $lum($toSrgb($y));

        return $a > $b ? ($a + 0.05) / ($b + 0.05) : ($b + 0.05) / ($a + 0.05);
    };

    $themes = [
        'light' => $tokensIn('/:root\s*\{([\s\S]*?)\n    \}/'),
        'dark' => $tokensIn('/\.dark\s*\{([\s\S]*?)\n    \}/'),
    ];

    $problems = [];

    foreach ($themes as $theme => $tokens) {
        foreach (['--bg', '--bg-elevated', '--bg-sunken'] as $ground) {
            foreach (['--text', '--text-muted', '--text-faint', '--accent'] as $ink) {
                $contrast = $ratio($tokens[$ink], $tokens[$ground]);

                // Body copy needs AA; faint metadata and UI marks need AA-large.
                $floor = in_array($ink, ['--text', '--text-muted'], true) ? 4.5 : 3.0;

                if ($contrast < $floor) {
                    $problems[] = sprintf('%s %s on %s is %.2f, below %.1f', $theme, $ink, $ground, $contrast, $floor);
                }

                if ($ink === '--text' && $contrast > 13.0) {
                    $problems[] = sprintf('%s %s on %s is %.2f — harsh, keep it under 13', $theme, $ink, $ground, $contrast);
                }
            }
        }

        // A filled button's label must be readable on the fill.
        $button = $ratio($tokens['--accent-text'], $tokens['--accent-fill']);

        if ($button < 4.5) {
            $problems[] = sprintf('%s button label on --accent-fill is %.2f, below 4.5', $theme, $button);
        }
    }

    expect($problems)->toBeEmpty(implode('; ', $problems));
});
