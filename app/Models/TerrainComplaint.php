<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerrainComplaint extends Model
{
    /** @use HasFactory<\Database\Factories\TerrainComplaintFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'type',
        'description',
        'status',
        'response',
        'date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: filter by type (complaint or proposition)
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: filter by user (agent terrain)
     */
    public function scopeFromAgent($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Human-readable type label
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'complaint' => 'Plainte',
            'proposition' => 'Proposition',
            default => $this->type,
        };
    }

    /**
     * Human-readable status label
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'reviewed' => 'Examinée',
            'resolved' => 'Résolue',
            default => $this->status,
        };
    }
}

