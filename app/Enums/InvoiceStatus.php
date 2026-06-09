<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Brouillon = 'brouillon';
    case Emise = 'emise';
    case Partielle = 'partielle';
    case Payee = 'payee';
    case Annulee = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Emise => 'Émise',
            self::Partielle => 'Partiellement payée',
            self::Payee => 'Payée',
            self::Annulee => 'Annulée',
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
