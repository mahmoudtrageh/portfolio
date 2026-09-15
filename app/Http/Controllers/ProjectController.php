<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Content;
use App\Support\Markdown;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A project's own case-study page.
 */
final class ProjectController extends Controller
{
    /**
     * The long-form sections, in the order they read on the page.
     *
     * @var list<string>
     */
    public const SECTIONS = ['context', 'challenges', 'solution', 'results', 'lessons'];

    public function __invoke(string $locale, string $slug): View
    {
        $projects = Content::list('projects');

        $index = null;

        foreach ($projects as $i => $candidate) {
            if (($candidate['slug'] ?? '') === $slug) {
                $index = $i;

                break;
            }
        }

        // A project with no case-study content has no page worth showing, so it
        // 404s rather than rendering a title over an empty body.
        if ($index === null || ! self::hasCaseStudy($projects[$index])) {
            throw new NotFoundHttpException;
        }

        $project = $projects[$index];

        return view('projects.show', [
            'project' => $project,
            // Rendered once here rather than in the view, so the template stays
            // free of logic and the sections are easy to iterate.
            'sections' => self::rendered($project),
            'next' => self::neighbour($projects, $index, 1),
            'previous' => self::neighbour($projects, $index, -1),
        ]);
    }

    /**
     * Does this project have anything to show on a page of its own?
     *
     * @param  array<string, mixed>  $project
     */
    public static function hasCaseStudy(array $project): bool
    {
        foreach (self::SECTIONS as $section) {
            if (trim((string) ($project[$section] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * The filled-in sections, converted from Markdown.
     *
     * @param  array<string, mixed>  $project
     * @return array<string, string>
     */
    private static function rendered(array $project): array
    {
        $sections = [];

        foreach (self::SECTIONS as $section) {
            $body = trim((string) ($project[$section] ?? ''));

            if ($body !== '') {
                $sections[$section] = Markdown::toHtml($body);
            }
        }

        return $sections;
    }

    /**
     * The next or previous project that also has a page, wrapping around.
     *
     * @param  list<array<string, mixed>>  $projects
     * @return array<string, mixed>|null
     */
    private static function neighbour(array $projects, int $index, int $direction): ?array
    {
        $count = count($projects);

        for ($step = 1; $step < $count; $step++) {
            // Modulo in PHP keeps the sign of the dividend, so a negative
            // direction needs the offset normalised before wrapping.
            $position = (($index + ($direction * $step)) % $count + $count) % $count;

            if (self::hasCaseStudy($projects[$position])) {
                return $projects[$position];
            }
        }

        return null;
    }
}
