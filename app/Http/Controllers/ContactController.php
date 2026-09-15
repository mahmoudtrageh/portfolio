<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

final class ContactController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            // Honeypot: bots fill every field, humans never see this one.
            'website' => ['nullable', 'prohibited'],
        ]);

        Mail::to(config('portfolio.identity.email'))
            ->send(new ContactMessage(
                senderName: $data['name'],
                senderEmail: $data['email'],
                body: $data['message'],
            ));

        return back()->with('contact.sent', true);
    }
}
