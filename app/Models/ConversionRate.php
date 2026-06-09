<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionRate extends Model
{
    protected $fillable = [
        'taux_fc',
        'defined_by',
        'actif',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'taux_fc' => 'decimal:4',
            'actif' => 'boolean',
        ];
    }

    public function definer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'defined_by');
    }

    /**
     * The currently active USD -> FC rate, or null if none defined yet.
     */
    public static function current(): ?self
    {
        return static::query()->where('actif', true)->latest()->first();
    }
}
