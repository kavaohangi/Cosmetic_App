<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

function chefMarketing(): User
{
    SpatieRole::findOrCreate(Role::ChefMarketing->value, 'web');
    $chef = User::factory()->create(['role' => Role::ChefMarketing]);
    $chef->assignRole(Role::ChefMarketing->value);

    return $chef;
}

it('stores the chef feedback (rejection reason) when rejecting an order', function () {
    $chef = chefMarketing();
    $agent = User::factory()->create(['role' => Role::AgentMarketeur]);
    $client = Client::factory()->create();

    $order = Order::create([
        'reference' => 'CMD-'.uniqid(),
        'client_id' => $client->id,
        'user_id' => $agent->id,
        'statut' => OrderStatus::EnAttente,
        'total' => 100,
        'date_commande' => today(),
    ]);

    $this->actingAs($chef)
        ->patch(route('orders.reject', $order), [
            'motif_rejet' => 'Prix non conforme au tarif validé.',
        ])
        ->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->statut)->toBe(OrderStatus::Annulee);
    expect($order->motif_rejet)->toBe('Prix non conforme au tarif validé.');
    expect($order->traite_par)->toBe($chef->id);
});
