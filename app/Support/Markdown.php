<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Renders post bodies from Markdown to HTML.
 *
 * Raw HTML in the source is escaped rather than passed through: post bodies are
 * stored content, and rendering them as markup would turn the editor into an
 * XSS vector the moment an account is compromised. Everything an article needs
 * — headings, lists, links, tables, code — is expressible in Markdown itself.
 *
 * Rendering is deterministic, so the result is cached by content hash and
 * reused until the post text actually changes.
 */
final class Markdown
{
    public static function toHtml(string $markdown): string
    {
        $markdown = trim($markdown);

        if ($markdown === '') {
            return '';
        }

        return (string) Cache::rememberForever(
            'markdown.'.hash('xxh128', $markdown),
            fn (): string => self::convert($markdown),
        );
    }

    private static function convert(string $markdown): string
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            // Deep nesting is a denial-of-service vector in Markdown parsers.
            'max_nesting_level' => 20,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        // Tables, strikethrough, task lists and autolinks.
        $environment->addExtension(new GithubFlavoredMarkdownExtension);

        return (new MarkdownConverter($environment))->convert($markdown)->getContent();
    }
}
