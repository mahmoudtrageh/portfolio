<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentSection;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    /**
     * The index: every section, with how many entries it holds.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'counts' => ContentSection::query()
                ->selectRaw('section, COUNT(*) as total')
                ->groupBy('section')
                ->pluck('total', 'section')
                ->all(),
        ]);
    }

    /**
     * Site-wide switches. Not a content section, so it has its own page.
     */
    public function settings(): View
    {
        return view('admin.settings');
    }

    /**
     * One section's editor. The Livewire component does the work.
     */
    public function section(string $section): View
    {
        abort_unless(array_key_exists($section, config('dashboard.sections')), 404);

        return view('admin.section', ['section' => $section]);
    }
}
