<?php

use App\Enums\Role;
use App\Models\DailyClosure;
use App\Models\User;
use App\Services\DailyClosureService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->service = app(DailyClosureService::class);
});

it('treats Sunday as a non-working day', function () {
    $sunday = CarbonImmutable::parse('2026-06-07'); // a Sunday
    expect($this->service->isWorkingDay($sunday))->toBeFalse();

    $monday = CarbonImmutable::parse('2026-06-08');
    expect($this->service->isWorkingDay($monday))->toBeTrue();
});

it('closes a working day and generates a report snapshot', function () {
    $user = User::factory()->create(['role' => Role::Magasinier]);
    $monday = CarbonImmutable::parse('2026-06-08');

    $closure = $this->service->close($user, $monday);

    expect($closure)->toBeInstanceOf(DailyClosure::class);
    expect($closure->payload['type'])->toBe('magasinier');
    expect($closure->day->is_closed)->toBeTrue();
});

it('refuses to close twice', function () {
    $user = User::factory()->create(['role' => Role::ChefMarketing]);
    $monday = CarbonImmutable::parse('2026-06-08');

    $this->service->close($user, $monday);

    expect(fn () => $this->service->close($user, $monday))
        ->toThrow(RuntimeException::class);
});

it('refuses to close on Sunday', function () {
    $user = User::factory()->create(['role' => Role::AgentMarketeur]);
    $sunday = CarbonImmutable::parse('2026-06-07');

    expect(fn () => $this->service->close($user, $sunday))
        ->toThrow(RuntimeException::class);
});
