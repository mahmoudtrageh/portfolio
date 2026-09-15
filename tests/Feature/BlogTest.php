<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Support\Markdown;

/**
 * A post live in both languages.
 */
function publishedPost(array $overrides = []): Post
{
    return Post::query()->create(array_merge([
        'title_ar' => 'عنوان عربي',
        'title_en' => 'An English title',
        'slug_ar' => 'عنوان-عربي',
        'slug_en' => 'an-english-title',
        'excerpt_ar' => 'مقتطف عربي.',
        'excerpt_en' => 'An English excerpt.',
        'body_ar' => "## عنوان فرعي\n\nنص المقال.",
        'body_en' => "## A heading\n\nThe body of the post.",
        'published_ar' => true,
        'published_en' => true,
        'published_at' => now()->subDay(),
    ], $overrides));
}

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

/**
 * Flip the blog switch on the settings section.
 */
function setBlogEnabled(bool $enabled): void
{
    setSetting('blog_enabled', $enabled);
}

/*
| Reading
*/

it('lists published posts on the blog index', function (string $locale): void {
    $post = publishedPost();

    $this->get("/{$locale}/blog")
        ->assertOk()
        ->assertSee($post->title($locale));
})->with(['ar', 'en']);

it('shows a post at its own slug in each locale', function (string $locale): void {
    $post = publishedPost();

    $this->get("/{$locale}/blog/".$post->slug($locale))
        ->assertOk()
        ->assertSee($post->title($locale));
})->with(['ar', 'en']);

it('renders the body from Markdown', function (): void {
    $post = publishedPost(['body_en' => "## Heading\n\nText with **bold** and `code`."]);

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertSee('<h2>Heading</h2>', escape: false)
        ->assertSee('<strong>bold</strong>', escape: false)
        ->assertSee('<code>code</code>', escape: false);
});

it('escapes raw HTML in a post body', function (): void {
    // Post bodies are stored content; rendering them as markup would make the
    // editor an XSS vector the moment an account is compromised.
    $post = publishedPost(['body_en' => 'Hello <script>alert(1)</script> world']);

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', escape: false);
});

/*
| Publish state
*/

it('hides a post that is not published in this locale', function (): void {
    $post = publishedPost(['published_en' => false]);

    // Live in Arabic, draft in English.
    $this->get('/ar/blog/'.$post->slug_ar)->assertOk();
    $this->get('/en/blog/'.$post->slug_en)->assertNotFound();
    $this->get('/en/blog')->assertOk()->assertDontSee($post->title_en);
});

it('hides a post whose publish date is in the future', function (): void {
    $post = publishedPost(['published_at' => now()->addWeek()]);

    $this->get('/en/blog/'.$post->slug_en)->assertNotFound();
    $this->get('/en/blog')->assertOk()->assertDontSee($post->title_en);
});

it('hides a post with no publish date at all', function (): void {
    $post = publishedPost(['published_at' => null]);

    $this->get('/en/blog/'.$post->slug_en)->assertNotFound();
});

it('404s on an unknown slug', function (): void {
    $this->get('/en/blog/no-such-post')->assertNotFound();
});

it('orders the index newest first', function (): void {
    $older = publishedPost(['slug_en' => 'older', 'title_en' => 'Older', 'published_at' => now()->subMonth()]);
    $newer = publishedPost(['slug_en' => 'newer', 'title_en' => 'Newer', 'slug_ar' => 'newer-ar', 'published_at' => now()->subHour()]);

    $html = (string) $this->get('/en/blog')->assertOk()->getContent();

    expect(strpos($html, $newer->title_en))->toBeLessThan(strpos($html, $older->title_en));
});

/*
| SEO
*/

it('gives a post article metadata and a canonical URL', function (): void {
    $post = publishedPost();
    $url = route('post', ['locale' => 'en', 'slug' => $post->slug_en]);

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertSee('<meta property="og:type" content="article">', escape: false)
        ->assertSee('<link rel="canonical" href="'.$url.'">', escape: false)
        ->assertSee($post->excerpt_en);
});

it('points hreflang at the translated slug, not the current one', function (): void {
    // Swapping the locale segment would advertise the English slug under /ar,
    // which 404s — the bug this guards against.
    $post = publishedPost();

    $arUrl = route('post', ['locale' => 'ar', 'slug' => $post->slug_ar]);

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertSee('hreflang="ar" href="'.$arUrl.'"', escape: false);
});

it('emits no hreflang link for a post published in one language only', function (): void {
    $post = publishedPost(['published_ar' => false]);

    $html = (string) $this->get('/en/blog/'.$post->slug_en)->assertOk()->getContent();

    // Only <link rel="alternate"> is an SEO signal. The navbar's language
    // switcher is an <a hreflang> — a UI hint, and not what crawlers read.
    preg_match_all('/<link[^>]+hreflang="([a-z-]+)"/', $html, $matches);

    expect($matches[1])->toBeEmpty();
});

it('publishes valid JSON-LD for a post', function (): void {
    $post = publishedPost();

    $html = (string) $this->get('/en/blog/'.$post->slug_en)->assertOk()->getContent();

    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    expect($m[1] ?? '')->not->toBe('');

    $json = json_decode(trim($m[1]), true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE)
        ->and(collect($json)->pluck('@type')->all())
        ->toBe(['BlogPosting', 'BreadcrumbList']);
});

it('lists published posts in the sitemap, and excludes drafts', function (): void {
    $live = publishedPost();
    $draft = publishedPost([
        'slug_en' => 'a-draft', 'slug_ar' => 'مسودة',
        'published_en' => false, 'published_ar' => false,
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('post', ['locale' => 'en', 'slug' => $live->slug_en]), escape: false)
        ->assertDontSee('a-draft', escape: false);
});

it('serves an RSS feed per locale', function (string $locale): void {
    $post = publishedPost();

    $response = $this->get("/{$locale}/feed.xml")->assertOk();

    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee($post->title($locale))
        ->assertSee('<language>'.$locale.'</language>', escape: false);

    // The feed must be well-formed XML, or readers reject it outright.
    expect(simplexml_load_string((string) $response->getContent()))->not->toBeFalse();
})->with(['ar', 'en']);

it('links the feed from the blog pages', function (): void {
    publishedPost();

    $this->get('/en/blog')
        ->assertOk()
        ->assertSee('type="application/rss+xml"', escape: false);
});

/*
| Homepage
*/

it('shows the latest posts on the homepage', function (): void {
    $post = publishedPost();

    $this->get('/en')
        ->assertOk()
        ->assertSee($post->title_en)
        ->assertSee('id="blog"', escape: false);
});

it('omits the homepage blog section when nothing is published', function (): void {
    $this->get('/en')->assertOk()->assertDontSee('id="blog"', escape: false);
});

it('resolves in-page anchors against the homepage from the blog', function (): void {
    // A bare "#work" on /blog would scroll nowhere.
    publishedPost();

    $this->get('/en/blog')
        ->assertOk()
        ->assertSee(route('home', ['locale' => 'en']).'#work', escape: false);
});

it('sends the language switcher to the translated post', function (): void {
    $post = publishedPost();

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertSee(route('post', ['locale' => 'ar', 'slug' => $post->slug_ar]), escape: false);
});

it('falls back to the blog index when a post has no translation', function (): void {
    $post = publishedPost(['published_ar' => false]);

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertSee(route('blog', ['locale' => 'ar']), escape: false);
});

/*
| Markdown helper
*/

it('caches rendered Markdown by content', function (): void {
    $first = Markdown::toHtml('## Same');
    $second = Markdown::toHtml('## Same');

    expect($first)->toBe($second)->and($first)->toContain('<h2>Same</h2>');
});

it('renders an empty body as an empty string', function (): void {
    expect(Markdown::toHtml('   '))->toBe('');
});

/*
| The blog switch
*/

it('serves the blog while it is enabled', function (): void {
    publishedPost();

    $this->get('/en/blog')->assertOk();
});

it('404s the blog index and its posts once it is disabled', function (): void {
    $post = publishedPost();

    setBlogEnabled(false);

    $this->get('/en/blog')->assertNotFound();
    $this->get('/en/blog/'.$post->slug_en)->assertNotFound();
    $this->get('/ar/blog')->assertNotFound();
});

it('404s the RSS feed once the blog is disabled', function (): void {
    publishedPost();

    $this->get('/en/feed.xml')->assertOk();

    setBlogEnabled(false);

    $this->get('/en/feed.xml')->assertNotFound();
});

it('drops the blog link and teaser from the site once disabled', function (): void {
    $post = publishedPost();

    setBlogEnabled(false);

    $response = $this->get('/en')->assertOk();

    $response->assertDontSee(route('blog', ['locale' => 'en']), escape: false);
    $response->assertDontSee($post->title_en);
    // The feed discovery tag goes with it, so nothing advertises a 404.
    $response->assertDontSee(route('feed', ['locale' => 'en']), escape: false);
});

it('drops blog URLs from the sitemap once disabled', function (): void {
    $post = publishedPost();

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('post', ['locale' => 'en', 'slug' => $post->slug_en]), escape: false);

    setBlogEnabled(false);

    $response = $this->get('/sitemap.xml')->assertOk();

    $response->assertDontSee(route('blog', ['locale' => 'en']), escape: false);
    $response->assertDontSee(route('post', ['locale' => 'en', 'slug' => $post->slug_en]), escape: false);
    // The rest of the site is untouched.
    $response->assertSee(route('home', ['locale' => 'en']), escape: false);
});

it('keeps the posts, so enabling the blog again restores them', function (): void {
    $post = publishedPost();

    setBlogEnabled(false);
    setBlogEnabled(true);

    $this->get('/en/blog/'.$post->slug_en)
        ->assertOk()
        ->assertSee($post->title_en);
});

it('reads as enabled when the switch has never been set', function (): void {
    forgetSettings();

    publishedPost();

    $this->get('/en/blog')->assertOk();
});

it('still lets an admin write posts while the blog is disabled', function (): void {
    setBlogEnabled(false);

    $this->actingAs($this->admin)
        ->get('/admin/posts')
        ->assertOk();
});
