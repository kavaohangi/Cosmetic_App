<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Active = 'active';
    case Consommee = 'consommee';
    case Liberee = 'liberee';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Consommee => 'Consommée',
            self::Liberee => 'Libérée',
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
