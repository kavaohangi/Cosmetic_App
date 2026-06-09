<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DayEntity extends Model
{
    protected $table = 'day_entities';

    protected $fillable = [
        'date',
        'is_closed',
        'closed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_closed' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function closures(): HasMany
    {
        return $this->hasMany(DailyClosure::class, 'day_id');
    }

    /**
     * Get or create the day entity for the given date (the mandatory first
     * step before any closure can be performed).
     */
    public static function forDate(CarbonInterface $date): self
    {
        return static::firstOrCreate(
            ['date' => $date->toDateString()],
            ['is_closed' => false],
        );
    }
}
