<?php

declare(strict_types=1);

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

it('sends a message and confirms it to the visitor', function (string $locale): void {
    $this->from("/{$locale}")
        ->post("/{$locale}/contact", [
            'name' => 'Sara Ahmed',
            'email' => 'sara@example.com',
            'message' => 'I would like to discuss a multi-tenant project with you.',
        ])
        ->assertRedirect("/{$locale}")
        ->assertSessionHas('contact.sent');

    Mail::assertSent(ContactMessage::class, function (ContactMessage $mail): bool {
        return $mail->senderEmail === 'sara@example.com'
            && $mail->hasTo(config('portfolio.identity.email'));
    });
})->with(['ar', 'en']);

it('rejects an invalid submission without sending mail', function (array $payload, string $field): void {
    $this->post('/ar/contact', $payload)->assertSessionHasErrors($field);

    Mail::assertNothingSent();
})->with([
    'missing name' => [['email' => 'a@b.com', 'message' => 'A long enough message.'], 'name'],
    'bad email' => [['name' => 'A', 'email' => 'nope', 'message' => 'A long enough message.'], 'email'],
    'short message' => [['name' => 'A', 'email' => 'a@b.com', 'message' => 'hi'], 'message'],
]);

it('drops submissions that fill the honeypot', function (): void {
    $this->post('/ar/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'Buy cheap backlinks right now from our website.',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    Mail::assertNothingSent();
});

it('throttles repeated submissions', function (): void {
    $payload = [
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'message' => 'A perfectly valid enquiry message.',
    ];

    foreach (range(1, 5) as $ignored) {
        $this->post('/ar/contact', $payload)->assertRedirect();
    }

    $this->post('/ar/contact', $payload)->assertStatus(429);
});
