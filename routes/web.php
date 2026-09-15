<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// One feed per locale, so a subscriber gets the language they chose.
Route::get('/{locale}/feed.xml', FeedController::class)
    ->whereIn('locale', config('portfolio.locales'))
    ->middleware(['locale', 'blog'])
    ->name('feed');

/*
| Dashboard
|
| Declared before the {locale} group below, which would otherwise swallow
| /admin as a locale segment.
*/
Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'show'])->name('login');

        Route::post('/login', [LoginController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::middleware('auth')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Posts are their own resource, not a content section, so they are
        // declared before the {section} catch-all below.
        Route::get('/posts', [PostController::class, 'index'])->name('posts');
        Route::get('/posts/new', [PostController::class, 'create'])->name('posts.create');
        Route::get('/posts/{post}', [PostController::class, 'edit'])->name('posts.edit');

        Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');

        // Contact form submissions. Declared before the {section} catch-all.
        Route::get('/messages', [DashboardController::class, 'messages'])->name('messages');

        Route::post('/messages/{message}', [DashboardController::class, 'message'])
            ->name('messages.update');

        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/{section}', [DashboardController::class, 'section'])->name('section');
    });
});

// Bare paths fall through to the default locale so old/short links keep working.
Route::redirect('/', '/'.config('app.locale'));

Route::prefix('{locale}')
    ->whereIn('locale', config('portfolio.locales'))
    ->middleware('locale')
    ->group(function (): void {
        Route::get('/', PageController::class)->name('home');

        // Both 404 while the blog is switched off (Identity → Blog).
        Route::middleware('blog')->group(function (): void {
            Route::get('/blog', [BlogController::class, 'index'])->name('blog');
            Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('post');
        });

        Route::get('/projects/{slug}', ProjectController::class)->name('project');

        Route::post('/contact', ContactController::class)
            ->middleware('throttle:5,1')
            ->name('contact.send');
    });
