<?php

use App\Enums\Role;
use App\Models\ConversionRate;
use App\Models\User;
use App\Services\ConversionService;
use Spatie\Permission\Models\Role as SpatieRole;

function makeUser(Role $role, array $attrs = []): User
{
    SpatieRole::findOrCreate($role->value, 'web');
    $user = User::factory()->create([...$attrs, 'role' => $role]);
    $user->assignRole($role->value);

    return $user;
}

it('lets the chef marketing set the USD to FC rate and activates only one', function () {
    $chef = makeUser(Role::ChefMarketing);

    $this->actingAs($chef)
        ->post(route('conversion-rates.store'), ['taux_fc' => 2800])
        ->assertRedirect(route('conversion-rates.index'));

    $this->actingAs($chef)
        ->post(route('conversion-rates.store'), ['taux_fc' => 2900])
        ->assertRedirect();

    expect(ConversionRate::where('actif', true)->count())->toBe(1);
    expect((float) ConversionRate::current()->taux_fc)->toBe(2900.0);
    expect(app(ConversionService::class)->currentRate())->toBe(2900.0);
});

it('formats money in USD and FC using the active rate', function () {
    $chef = makeUser(Role::ChefMarketing);
    $this->actingAs($chef)->post(route('conversion-rates.store'), ['taux_fc' => 2500]);

    expect(app(ConversionService::class)->format(10.0))->toContain('25 000 FC');
});

it('lets a supervisor toggle a team member active state', function () {
    $chef = makeUser(Role::ChefMarketing);
    $agent = makeUser(Role::AgentMarketeur, ['supervisor_id' => $chef->id, 'is_active' => true]);

    $this->actingAs($chef)
        ->patch(route('agents.toggle-active', $agent))
        ->assertRedirect();

    expect($agent->refresh()->is_active)->toBeFalse();
});
