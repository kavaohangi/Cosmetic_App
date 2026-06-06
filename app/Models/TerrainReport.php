<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerrainReport extends Model
{
    /** @use HasFactory<\Database\Factories\TerrainReportFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'supervisor_id',
        'date',
        'nb_ventes',
        'photo_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'nb_ventes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TerrainReportItem::class);
    }

    /**
     * Total revenue reported on this terrain report.
     */
    protected function montantTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): float => (float) $this->items->sum('sous_total'),
        );
    }
}
