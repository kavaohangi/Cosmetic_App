<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsIssueLine extends Model
{
    protected $fillable = [
        'goods_issue_note_id',
        'product_id',
        'quantite',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(GoodsIssueNote::class, 'goods_issue_note_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
