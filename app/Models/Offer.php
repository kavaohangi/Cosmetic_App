<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'product_id',
        'remise_pourcentage',
        'date_debut',
        'date_fin',
        'actif',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'remise_pourcentage' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'actif' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Offers currently active and within their date window.
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('actif', true)
            ->where(fn (Builder $q) => $q->whereNull('date_debut')->orWhereDate('date_debut', '<=', today()))
            ->where(fn (Builder $q) => $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', today()));
    }
}
