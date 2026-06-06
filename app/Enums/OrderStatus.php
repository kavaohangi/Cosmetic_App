<?php

namespace App\Enums;

enum OrderStatus: string
{
    case EnAttente = 'en_attente';
    case Validee = 'validee';
    case EnPreparation = 'en_preparation';
    case Livree = 'livree';
    case Annulee = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Validee => 'Validée',
            self::EnPreparation => 'En préparation',
            self::Livree => 'Livrée',
            self::Annulee => 'Annulée',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
