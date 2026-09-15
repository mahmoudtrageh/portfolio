<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A message sent through the contact form.
 *
 * Stored before the notification email is attempted, so a mail failure loses
 * the notification but never the message itself.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $body
 * @property string|null $ip
 * @property string|null $locale
 * @property bool $mailed
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'body', 'ip', 'locale', 'mailed', 'read_at'])]
final class ContactMessage extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mailed' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /** @param  Builder<self>  $query */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /** @param  Builder<self>  $query */
    public function scopeNewest(Builder $query): void
    {
        $query->orderByDesc('created_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
