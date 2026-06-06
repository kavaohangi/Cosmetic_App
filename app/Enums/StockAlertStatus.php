<?php

namespace App\Enums;

enum StockAlertStatus: string
{
    case EnAttente = 'en_attente';
    case Resolu = 'resolu';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Resolu => 'Résolu',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $s): string => $s->value, self::cases());
    }
}
