<?php

namespace App\Enums;

enum SaleType: string
{
    case Comptant = 'comptant';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Comptant => 'Comptant',
            self::Credit => 'Crédit',
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
