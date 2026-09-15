<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Contact form submissions.
|
| These were previously only emailed, so a mail failure — an unconfigured SMTP
| host, a provider outage — lost the message with nothing to recover. Storing
| them first makes the database the record and the email a notification, and
| gives the dashboard something to show.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 120);
            $table->string('email', 190);
            $table->text('body');

            // Kept for spam triage: a burst from one address is easy to spot.
            $table->string('ip', 45)->nullable();
            $table->string('locale', 5)->nullable();

            // Whether the notification email actually went out. A message can
            // be stored and still un-emailed, and the dashboard says so.
            $table->boolean('mailed')->default(false);

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // The dashboard lists newest first and badges the unread count.
            $table->index(['read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
