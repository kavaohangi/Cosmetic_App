<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\SaleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'reference',
        'client_id',
        'agent_id',
        'order_id',
        'statut',
        'type_vente',
        'montant',
        'montant_paye',
        'date',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statut' => InvoiceStatus::class,
            'type_vente' => SaleType::class,
            'montant' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function resteAPayer(): float
    {
        return max(0, (float) $this->montant - (float) $this->montant_paye);
    }

    /**
     * Outstanding credit invoices (client owes money).
     *
     * @param  Builder<self>  $query
     */
    public function scopeCredit(Builder $query): Builder
    {
        return $query->where('type_vente', SaleType::Credit->value)
            ->whereIn('statut', [InvoiceStatus::Emise->value, InvoiceStatus::Partielle->value]);
    }
}
