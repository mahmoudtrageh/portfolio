<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One entry of site content — a singleton section's only row, or one item in
 * an ordered list.
 *
 * @property int $id
 * @property string $section
 * @property int $position
 * @property array<string, mixed> $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['section', 'position', 'data'])]
final class ContentSection extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    /**
     * @param  Builder<ContentSection>  $query
     * @return Builder<ContentSection>
     */
    public function scopeSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section)->orderBy('position');
    }
}
