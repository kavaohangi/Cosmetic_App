<?php

use App\Services\DocumentNumberService;
use Illuminate\Support\Carbon;

it('builds a goods issue reference with agent/client initials, date, time and increasing number', function () {
    $service = app(DocumentNumberService::class);
    $moment = Carbon::create(2026, 6, 9, 14, 7);

    $ref1 = $service->goodsIssueReference('Jean Petit', 'Boutique Alpha', $moment);
    $ref2 = $service->goodsIssueReference('Jean Petit', 'Boutique Alpha', $moment);

    expect($ref1)->toBe('JP-BA-20260609-1407-0001');
    expect($ref2)->toBe('JP-BA-20260609-1407-0002');
});

it('produces unique sequential numbers for the same scope', function () {
    $service = app(DocumentNumberService::class);

    $numbers = collect(range(1, 5))->map(fn () => $service->nextNumber('BS-20260609'));

    expect($numbers->all())->toBe([1, 2, 3, 4, 5]);
});

it('handles single-word names with padded initials', function () {
    $service = app(DocumentNumberService::class);

    expect($service->initials('Madonna'))->toBe('MA');
    expect($service->initials('Éric Dupont'))->toBe('ED');
});
