<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosure extends Model
{
    protected $fillable = [
        'day_id',
        'user_id',
        'role',
        'ventes_credit',
        'ventes_comptant',
        'payload',
        'closed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'ventes_credit' => 'decimal:2',
            'ventes_comptant' => 'decimal:2',
            'payload' => 'array',
            'closed_at' => 'datetime',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(DayEntity::class, 'day_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
