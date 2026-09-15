<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;

final class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::query()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.edit', ['post' => null]);
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', ['post' => $post]);
    }
}
