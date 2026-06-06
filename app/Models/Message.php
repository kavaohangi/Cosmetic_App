<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'lu',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Conversation between two users (both directions).
     *
     * @param  Builder<self>  $query
     */
    public function scopeBetween(Builder $query, int $userA, int $userB): Builder
    {
        return $query->where(function (Builder $q) use ($userA, $userB): void {
            $q->where('sender_id', $userA)->where('receiver_id', $userB);
        })->orWhere(function (Builder $q) use ($userA, $userB): void {
            $q->where('sender_id', $userB)->where('receiver_id', $userA);
        });
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeNonLus(Builder $query): Builder
    {
        return $query->where('lu', false);
    }
}
