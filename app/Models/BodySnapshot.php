<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BodySnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'weight_kg',
        'bmi',
        'body_fat_percent',
        'muscle_percent',
        'water_percent',
        'origin',
        'photo_paths',
        'recorded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_kg' => 'float',
            'bmi' => 'float',
            'body_fat_percent' => 'float',
            'muscle_percent' => 'float',
            'water_percent' => 'float',
            'photo_paths' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
