<?php

declare(strict_types=1);

use App\Mail\ContactMessage as ContactMessageMail;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// The contact form used to only send an email, so a mail failure lost the
// message entirely. These cover the storage that replaced that.

it('stores a submitted message', function (): void {
    Mail::fake();

    $this->post('/en/contact', [
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'message' => 'I would like to discuss a backend project with you.',
    ])->assertRedirect();

    $message = ContactMessage::query()->first();

    expect($message)->not->toBeNull()
        ->and($message->name)->toBe('Sara')
        ->and($message->email)->toBe('sara@example.com')
        ->and($message->body)->toContain('backend project')
        ->and($message->read_at)->toBeNull();

    Mail::assertSent(ContactMessageMail::class);
});

it('keeps the message when the notification email fails', function (): void {
    // The whole point of storing first: an unconfigured or failing mailer
    // must not lose what somebody wrote.
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->post('/en/contact', [
        'name' => 'Omar',
        'email' => 'omar@example.com',
        'message' => 'Testing that a mail outage does not lose this message.',
    ])->assertRedirect();

    $message = ContactMessage::query()->first();

    expect($message)->not->toBeNull()
        ->and($message->mailed)->toBeFalse();
});

it('marks a message as mailed when sending succeeds', function (): void {
    Mail::fake();

    $this->post('/en/contact', [
        'name' => 'Lina',
        'email' => 'lina@example.com',
        'message' => 'A message that should be flagged as emailed.',
    ]);

    expect(ContactMessage::query()->first()->mailed)->toBeTrue();
});

it('does not store a honeypot submission', function (): void {
    Mail::fake();

    $this->post('/en/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'This submission filled the hidden field.',
        'website' => 'https://spam.example.com',
    ])->assertSessionHasErrors('website');

    expect(ContactMessage::query()->count())->toBe(0);
});

it('requires authentication to read messages', function (): void {
    ContactMessage::query()->create([
        'name' => 'Sara', 'email' => 'sara@example.com', 'body' => 'Private enquiry.',
    ]);

    $this->get('/admin/messages')->assertRedirect('/admin/login');
});

it('lists messages in the dashboard, newest first', function (): void {
    $this->actingAs(User::factory()->create());

    // created_at is not fillable, so it is set after insert — otherwise both
    // rows share a timestamp and the ordering assertion proves nothing.
    ContactMessage::query()->create([
        'name' => 'Older', 'email' => 'a@example.com', 'body' => 'First enquiry.',
    ])->forceFill(['created_at' => now()->subDay()])->save();

    ContactMessage::query()->create([
        'name' => 'Newer', 'email' => 'b@example.com', 'body' => 'Second enquiry.',
    ]);

    $this->get('/admin/messages')
        ->assertOk()
        ->assertSeeInOrder(['Newer', 'Older'])
        ->assertSee('First enquiry.');
});

it('marks a message read from the dashboard', function (): void {
    $this->actingAs(User::factory()->create());

    $message = ContactMessage::query()->create([
        'name' => 'Sara', 'email' => 'sara@example.com', 'body' => 'An enquiry to read.',
    ]);

    $this->post("/admin/messages/{$message->id}")->assertRedirect();

    expect($message->fresh()->read_at)->not->toBeNull();
});

it('deletes a message from the dashboard', function (): void {
    $this->actingAs(User::factory()->create());

    $message = ContactMessage::query()->create([
        'name' => 'Sara', 'email' => 'sara@example.com', 'body' => 'An enquiry to delete.',
    ]);

    $this->post("/admin/messages/{$message->id}", ['delete' => '1'])->assertRedirect();

    expect(ContactMessage::query()->count())->toBe(0);
});
