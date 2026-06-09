<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Prepare = 'prepare';
    case Livre = 'livre';
    case Annule = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::Prepare => 'Préparé',
            self::Livre => 'Livré',
            self::Annule => 'Annulé',
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
