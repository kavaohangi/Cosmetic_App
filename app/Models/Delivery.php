<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\SaleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    protected $fillable = [
        'reference',
        'order_id',
        'client_id',
        'agent_id',
        'created_by',
        'invoice_id',
        'statut',
        'type_vente',
        'date',
        'delivered_by',
        'delivered_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statut' => DeliveryStatus::class,
            'type_vente' => SaleType::class,
            'date' => 'date',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryLine::class);
    }

    public function goodsIssueNote(): HasOne
    {
        return $this->hasOne(GoodsIssueNote::class);
    }

    /**
     * Total value of the delivery (net of returns), in USD.
     */
    public function montantTotal(): float
    {
        return (float) $this->lines->sum(
            fn (DeliveryLine $line): float => (float) $line->prix_unitaire * max(0, $line->quantite - $line->quantite_rendue)
        );
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeLivre(Builder $query): Builder
    {
        return $query->where('statut', DeliveryStatus::Livre->value);
    }
}
