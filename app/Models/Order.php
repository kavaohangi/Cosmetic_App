<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'reference',
        'client_id',
        'user_id',
        'traite_par',
        'traite_le',
        'statut',
        'total',
        'date_commande',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statut' => OrderStatus::class,
            'total' => 'decimal:2',
            'date_commande' => 'date',
            'traite_le' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The staff member (Chef Marketing / Admin) who validated or rejected the order.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Pending orders.
     *
     * @param  Builder<self>  $query
     */
    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->where('statut', OrderStatus::EnAttente->value);
    }
}
