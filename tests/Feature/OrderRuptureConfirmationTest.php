<?php

use App\Enums\Role;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

function agentUser(): User
{
    SpatieRole::findOrCreate(Role::AgentMarketeur->value, 'web');
    $user = User::factory()->create(['role' => Role::AgentMarketeur]);
    $user->assignRole(Role::AgentMarketeur->value);

    return $user;
}

it('asks for confirmation when an ordered product is out of stock', function () {
    $agent = agentUser();
    $client = Client::factory()->create();
    $product = Product::factory()->create(['stock' => 1, 'stock_reserved' => 0, 'seuil_alerte' => 0]);

    $this->actingAs($agent)
        ->post(route('orders.store'), [
            'client_id' => $client->id,
            'date_commande' => today()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantite' => 5]],
        ])
        ->assertRedirect()
        ->assertSessionHas('rupture_confirmation');

    expect(Order::count())->toBe(0);
});

it('creates the order and keeps the ruptured item once confirmed', function () {
    $agent = agentUser();
    $client = Client::factory()->create();
    $product = Product::factory()->create(['stock' => 1, 'stock_reserved' => 0, 'seuil_alerte' => 0]);

    $this->actingAs($agent)
        ->post(route('orders.store'), [
            'client_id' => $client->id,
            'date_commande' => today()->toDateString(),
            'confirm_rupture' => 1,
            'items' => [['product_id' => $product->id, 'quantite' => 5]],
        ])
        ->assertRedirect();

    $order = Order::first();
    expect($order)->not->toBeNull();
    expect($order->items()->count())->toBe(1);

    $this->assertDatabaseHas('stock_alerts', [
        'product_id' => $product->id,
        'order_id' => $order->id,
    ]);
});
