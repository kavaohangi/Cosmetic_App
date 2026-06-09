<?php

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

function agentMarketeur(): User
{
    foreach ([Role::AgentMarketeur, Role::Client] as $role) {
        SpatieRole::findOrCreate($role->value, 'web');
    }

    $agent = User::factory()->create(['role' => Role::AgentMarketeur, 'magasin' => null]);
    $agent->assignRole(Role::AgentMarketeur->value);

    return $agent;
}

it('creates a client together with a login account and flashes credentials', function () {
    $agent = agentMarketeur();

    $response = $this->actingAs($agent)->post(route('clients.store'), [
        'name' => 'Boutique Alpha',
        'email' => 'alpha@example.com',
        'phone' => '+243000000',
        'ville' => 'Kinshasa',
    ]);

    $client = Client::first();

    expect($client)->not->toBeNull();
    expect($client->user_id)->not->toBeNull();
    expect($client->agent_id)->toBe($agent->id);

    $account = User::find($client->user_id);
    expect($account->role)->toBe(Role::Client);
    expect($account->hasRole(Role::Client->value))->toBeTrue();

    $response->assertSessionHas('client_credentials');
});

it('generates a unique login when the client email is already used by a user', function () {
    $agent = agentMarketeur();
    User::factory()->create(['email' => 'dup@example.com']);

    $this->actingAs($agent)->post(route('clients.store'), [
        'name' => 'Client Sans Email',
        'email' => 'dup@example.com',
    ]);

    $client = Client::first();
    $account = User::find($client->user_id);

    expect($account->email)->not->toBe('dup@example.com');
});
