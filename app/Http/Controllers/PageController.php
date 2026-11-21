<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Content;
use App\Support\Profile;
use Illuminate\Contracts\View\View;

/**
 * The three top-level pages: a short introduction, the projects, and contact.
 */
final class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'current' => Profile::current(),
            'previous' => Profile::previous(),
            // Latest posts, teasing the blog from the homepage.
            'posts' => Content::blogEnabled()
                ? Post::query()->published()->limit(3)->get()
                : collect(),
        ]);
    }

    public function projects(): View
    {
        return view('pages.projects', [
            'groups' => Profile::projectGroups(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'contacts' => Profile::contacts(),
        ]);
    }
}
