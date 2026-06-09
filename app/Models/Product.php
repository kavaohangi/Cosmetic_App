<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'price',
        'stock',
        'stock_reserved',
        'seuil_alerte',
        'image_url',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'stock_reserved' => 'integer',
            'seuil_alerte' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Available stock = physical stock minus the quantity already reserved.
     */
    protected function disponible(): Attribute
    {
        return Attribute::make(
            get: fn (): int => max(0, (int) $this->stock - (int) $this->stock_reserved),
        );
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }

    /**
     * Products at or below their alert threshold (rupture de stock).
     *
     * @param  Builder<self>  $query
     */
    public function scopeEnRupture(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'seuil_alerte');
    }

    public function estEnRupture(): bool
    {
        return $this->stock <= $this->seuil_alerte;
    }
}
