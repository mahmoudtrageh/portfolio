<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactMessage as ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Stored first: the message survives even when mail is misconfigured
        // or the provider is down, and the dashboard is the record of it.
        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'body' => $data['message'],
            'ip' => $request->ip(),
            'locale' => app()->getLocale(),
        ]);

        // The email is a notification on top of that, so a failure here must
        // not lose the message or show the sender an error.
        try {
            Mail::to(config('portfolio.identity.email'))
                ->send(new ContactMessageMail(
                    senderName: $data['name'],
                    senderEmail: $data['email'],
                    body: $data['message'],
                ));

            $message->forceFill(['mailed' => true])->save();
        } catch (\Throwable $e) {
            Log::warning('Contact notification email failed.', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('contact.sent', true);
    }
}
