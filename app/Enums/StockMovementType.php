<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Entree = 'entree';
    case Sortie = 'sortie';
    case Ajustement = 'ajustement';
    case Reservation = 'reservation';
    case Liberation = 'liberation';

    public function label(): string
    {
        return match ($this) {
            self::Entree => 'Entrée',
            self::Sortie => 'Sortie',
            self::Ajustement => 'Ajustement',
            self::Reservation => 'Réservation',
            self::Liberation => 'Libération',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $t): string => $t->value, self::cases());
    }
}
