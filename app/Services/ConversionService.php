<?php

namespace App\Services;

use App\Models\ConversionRate;

/**
 * Currency conversion between the storage currency (USD) and the display
 * currency (FC / Congolese Franc). Prices are always stored in USD; FC is a
 * computed display value based on the company rate set by the Chef Marketing.
 */
class ConversionService
{
    private const CACHE_KEY = 'conversion_rate_current';

    /**
     * Current USD -> FC rate, or null when none has been defined yet.
     */
    public function currentRate(): ?float
    {
        $rate = cache()->remember(self::CACHE_KEY, now()->addMinutes(10), function (): ?float {
            return optional(ConversionRate::current())->taux_fc !== null
                ? (float) ConversionRate::current()->taux_fc
                : null;
        });

        return $rate;
    }

    /**
     * Convert a USD amount to FC. Returns null when no rate is defined.
     */
    public function toFc(float $usd): ?float
    {
        $rate = $this->currentRate();

        return $rate === null ? null : round($usd * $rate, 2);
    }

    /**
     * Format a USD amount, optionally appending the FC equivalent.
     */
    public function format(float $usd, bool $withFc = true): string
    {
        $label = '$'.number_format($usd, 2, '.', ' ');

        if ($withFc) {
            $fc = $this->toFc($usd);

            if ($fc !== null) {
                $label .= ' ('.number_format($fc, 0, ',', ' ').' FC)';
            }
        }

        return $label;
    }

    public static function flushCache(): void
    {
        cache()->forget(self::CACHE_KEY);
    }
}
