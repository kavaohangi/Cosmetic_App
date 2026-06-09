<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Generates business document references with an atomic, race-safe
 * incrementing counter scoped per (prefix + day).
 */
class DocumentNumberService
{
    /**
     * Build a goods issue note reference of the form:
     *   AA-CC-YYYYMMDD-HHmm-NNNN
     *
     * Example: JP-BA-20260609-1407-0003
     *
     * @param  string  $agentName  Full name of the marketing agent.
     * @param  string  $clientName  Full name of the client.
     */
    public function goodsIssueReference(string $agentName, string $clientName, ?Carbon $moment = null): string
    {
        $moment ??= now();

        $agentInitials = $this->initials($agentName);
        $clientInitials = $this->initials($clientName);
        $datePart = $moment->format('Ymd');
        $timePart = $moment->format('Hi');

        $scope = sprintf('BS-%s', $datePart);
        $number = $this->nextNumber($scope);

        return sprintf(
            '%s-%s-%s-%s-%04d',
            $agentInitials,
            $clientInitials,
            $datePart,
            $timePart,
            $number,
        );
    }

    /**
     * Atomically reserve and return the next number for a given scope.
     */
    public function nextNumber(string $scope): int
    {
        return DB::transaction(function () use ($scope): int {
            $sequence = DocumentSequence::query()
                ->where('scope', $scope)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = DocumentSequence::create(['scope' => $scope, 'last_number' => 0]);
                // Re-fetch with a lock to serialize concurrent first-inserts.
                $sequence = DocumentSequence::query()
                    ->where('scope', $scope)
                    ->lockForUpdate()
                    ->first();
            }

            $next = $sequence->last_number + 1;
            $sequence->update(['last_number' => $next]);

            return $next;
        });
    }

    /**
     * Two-letter uppercase initials from a name (accent-insensitive).
     */
    public function initials(string $name): string
    {
        $ascii = Str::upper(Str::ascii(trim($name)));
        $words = preg_split('/\s+/', $ascii, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) >= 2) {
            return mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1);
        }

        $single = $words[0] ?? 'XX';

        return str_pad(mb_substr($single, 0, 2), 2, 'X');
    }
}
