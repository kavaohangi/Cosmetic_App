<?php

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Enums\StockMovementType;
use App\Models\Client;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->service = app(InventoryService::class);
});

function makeOrderWithItem(Product $product, int $quantite): Order
{
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $order = Order::create([
        'reference' => 'CMD-TEST-'.uniqid(),
        'client_id' => $client->id,
        'user_id' => $user->id,
        'statut' => OrderStatus::EnAttente,
        'total' => $product->price * $quantite,
        'date_commande' => today(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantite' => $quantite,
        'prix_unitaire' => $product->price,
        'sous_total' => $product->price * $quantite,
    ]);

    return $order;
}

it('reserves stock without touching physical stock', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0]);
    $order = makeOrderWithItem($product, 10);

    $manquants = $this->service->reserveForOrder($order);

    expect($manquants)->toBe([]);

    $product->refresh();
    expect($product->stock)->toBe(50);
    expect($product->stock_reserved)->toBe(10);
    expect($product->disponible)->toBe(40);

    assertDatabaseHas('inventory_reservations', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantite' => 10,
        'statut' => ReservationStatus::Active->value,
    ]);

    assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovementType::Reservation->value,
        'quantite' => 10,
    ]);
});

it('refuses to reserve more than the available stock', function () {
    $product = Product::factory()->create(['stock' => 5, 'stock_reserved' => 0]);
    $order = makeOrderWithItem($product, 10);

    $manquants = $this->service->reserveForOrder($order);

    expect($manquants)->toHaveCount(1);
    expect($product->refresh()->stock_reserved)->toBe(0);
});

it('issues stock by decrementing physical and reserved', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0]);
    $order = makeOrderWithItem($product, 10);
    $this->service->reserveForOrder($order);

    $this->service->issue($product->refresh(), 10, $order, null, 'Bon de sortie test');

    $product->refresh();
    expect($product->stock)->toBe(40);
    expect($product->stock_reserved)->toBe(0);

    assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovementType::Sortie->value,
        'quantite' => -10,
    ]);
});

it('releases a reservation back to availability', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0]);
    $order = makeOrderWithItem($product, 10);
    $this->service->reserveForOrder($order);

    $this->service->releaseForOrder($order);

    $product->refresh();
    expect($product->stock)->toBe(50);
    expect($product->stock_reserved)->toBe(0);

    expect(InventoryReservation::where('order_id', $order->id)->where('statut', ReservationStatus::Liberee->value)->count())->toBe(1);
});

it('blocks issuing more than physical stock', function () {
    $product = Product::factory()->create(['stock' => 3, 'stock_reserved' => 0]);

    expect(fn () => $this->service->issue($product, 10))
        ->toThrow(RuntimeException::class);
});
