<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLine extends Model
{
    protected $fillable = [
        'delivery_id',
        'product_id',
        'quantite',
        'quantite_rendue',
        'prix_unitaire',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'quantite_rendue' => 'integer',
            'prix_unitaire' => 'decimal:2',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Net quantity that physically leaves the warehouse (delivered minus returned).
     */
    public function quantiteNette(): int
    {
        return max(0, (int) $this->quantite - (int) $this->quantite_rendue);
    }
}
