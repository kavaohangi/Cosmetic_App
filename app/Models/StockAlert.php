<?php

namespace App\Models;

use App\Enums\StockAlertStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    protected $fillable = [
        'product_id',
        'order_id',
        'created_by',
        'quantite_demandee',
        'quantite_disponible',
        'statut',
        'resolved_by',
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statut' => StockAlertStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->where('statut', StockAlertStatus::EnAttente->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeResolu(Builder $query): Builder
    {
        return $query->where('statut', StockAlertStatus::Resolu->value);
    }
}
