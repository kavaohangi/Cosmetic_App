<?php

use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

function rbacUserWithRole(Role $role): User
{
    SpatieRole::findOrCreate($role->value, 'web');
    SpatieRole::findOrCreate(Role::MarketeurTerrain->value, 'web');

    $user = User::factory()->create(['role' => $role, 'magasin' => null]);
    $user->assignRole($role->value);

    return $user;
}

it('forbids an agent marketeur from creating a goods issue (bon de sortie)', function () {
    $agent = rbacUserWithRole(Role::AgentMarketeur);

    $this->actingAs($agent)
        ->get(route('deliveries.create'))
        ->assertForbidden();
});

it('allows the chef marketing to access goods issue creation', function () {
    $chef = rbacUserWithRole(Role::ChefMarketing);

    $this->actingAs($chef)
        ->get(route('deliveries.create'))
        ->assertOk();
});

it('rejects creating a terrain agent without a magasin', function () {
    $agent = rbacUserWithRole(Role::AgentMarketeur);

    $this->actingAs($agent)
        ->post(route('agents.store'), [
            'name' => 'Terrain Sans Magasin',
            'email' => 'terrain@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('magasin');
});

it('creates a terrain agent when a magasin is provided', function () {
    $agent = rbacUserWithRole(Role::AgentMarketeur);

    $this->actingAs($agent)
        ->post(route('agents.store'), [
            'name' => 'Terrain Avec Magasin',
            'email' => 'terrain2@example.com',
            'magasin' => 'Boutique Centre',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'terrain2@example.com',
        'magasin' => 'Boutique Centre',
    ]);
});
