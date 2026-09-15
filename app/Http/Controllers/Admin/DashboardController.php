<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContentSection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
     * Messages sent through the contact form, newest first.
     */
    public function messages(): View
    {
        return view('admin.messages', [
            'messages' => ContactMessage::query()->newest()->paginate(20),
            'unread' => ContactMessage::query()->unread()->count(),
        ]);
    }

    /**
     * Mark one message read, or delete it.
     */
    public function message(Request $request, ContactMessage $message): RedirectResponse
    {
        if ($request->boolean('delete')) {
            $message->delete();

            return back()->with('status', __('Message deleted.'));
        }

        $message->markRead();

        return back();
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
