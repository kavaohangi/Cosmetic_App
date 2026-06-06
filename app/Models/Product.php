<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'price',
        'stock',
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
            'seuil_alerte' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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
