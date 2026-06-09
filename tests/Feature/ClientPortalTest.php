<?php

use App\Enums\Role;
use App\Models\Client;
use App\Models\Message;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

function clientWithAgent(): array
{
    foreach ([Role::Client, Role::AgentMarketeur] as $role) {
        SpatieRole::findOrCreate($role->value, 'web');
    }

    $agent = User::factory()->create(['role' => Role::AgentMarketeur, 'name' => 'Marie Agent']);
    $agent->assignRole(Role::AgentMarketeur->value);

    $account = User::factory()->create(['role' => Role::Client]);
    $account->assignRole(Role::Client->value);

    $client = Client::factory()->create(['user_id' => $account->id, 'agent_id' => $agent->id]);

    return [$account, $agent, $client];
}

it('shows the client dashboard', function () {
    [$account] = clientWithAgent();

    $this->actingAs($account)
        ->get(route('portal.dashboard'))
        ->assertOk()
        ->assertViewIs('portal.dashboard');
});

it('shows the assigned marketeur information', function () {
    [$account, $agent] = clientWithAgent();

    $this->actingAs($account)
        ->get(route('portal.marketeur'))
        ->assertOk()
        ->assertSee('Marie Agent');
});

it('lets the client send a message to their marketeur', function () {
    [$account, $agent] = clientWithAgent();

    $this->actingAs($account)
        ->post(route('portal.messages.send'), ['content' => 'Bonjour, une question.'])
        ->assertRedirect(route('portal.messages'));

    $this->assertDatabaseHas('messages', [
        'sender_id' => $account->id,
        'receiver_id' => $agent->id,
        'content' => 'Bonjour, une question.',
    ]);
});

it('marks the marketeur messages as read when the client opens the conversation', function () {
    [$account, $agent] = clientWithAgent();

    Message::create([
        'sender_id' => $agent->id,
        'receiver_id' => $account->id,
        'content' => 'Votre commande est validée.',
        'lu' => false,
    ]);

    $this->actingAs($account)
        ->get(route('portal.messages'))
        ->assertOk()
        ->assertSee('Votre commande est validée.');

    $this->assertDatabaseHas('messages', [
        'sender_id' => $agent->id,
        'receiver_id' => $account->id,
        'lu' => true,
    ]);
});

it('forbids messaging when no marketeur is assigned', function () {
    SpatieRole::findOrCreate(Role::Client->value, 'web');
    $account = User::factory()->create(['role' => Role::Client]);
    $account->assignRole(Role::Client->value);
    Client::factory()->create(['user_id' => $account->id, 'agent_id' => null]);

    $this->actingAs($account)
        ->get(route('portal.messages'))
        ->assertForbidden();
});
