<?php

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DeliveryService;
use App\Services\InventoryService;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->inventory = app(InventoryService::class);
    $this->deliveries = app(DeliveryService::class);
});

function validatedOrder(Product $product, int $qty): Order
{
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $order = Order::create([
        'reference' => 'CMD-'.uniqid(),
        'client_id' => $client->id,
        'user_id' => $user->id,
        'statut' => OrderStatus::Validee,
        'total' => $product->price * $qty,
        'date_commande' => today(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantite' => $qty,
        'prix_unitaire' => $product->price,
        'sous_total' => $product->price * $qty,
    ]);

    return $order;
}

it('creates a prepared delivery from a validated order', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0]);
    $order = validatedOrder($product, 10);
    $this->inventory->reserveForOrder($order);

    $delivery = $this->deliveries->createFromOrder($order, SaleType::Comptant);

    expect($delivery->statut)->toBe(DeliveryStatus::Prepare);
    expect($delivery->lines)->toHaveCount(1);
    expect($order->refresh()->statut)->toBe(OrderStatus::EnPreparation);
});

it('confirming a delivery issues physical stock and generates a goods issue note', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0]);
    $order = validatedOrder($product, 10);
    $this->inventory->reserveForOrder($order);
    $delivery = $this->deliveries->createFromOrder($order, SaleType::Comptant);

    $note = $this->deliveries->confirm($delivery);

    $product->refresh();
    expect($product->stock)->toBe(40);
    expect($product->stock_reserved)->toBe(0);
    expect($delivery->refresh()->statut)->toBe(DeliveryStatus::Livre);
    expect($order->refresh()->statut)->toBe(OrderStatus::Livree);

    assertDatabaseHas('goods_issue_notes', ['id' => $note->id, 'delivery_id' => $delivery->id]);
    assertDatabaseHas('goods_issue_lines', ['goods_issue_note_id' => $note->id, 'product_id' => $product->id, 'quantite' => 10]);
});

it('returns reduce the net quantity issued', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0]);
    $order = validatedOrder($product, 10);
    $this->inventory->reserveForOrder($order);
    $delivery = $this->deliveries->createFromOrder($order, SaleType::Comptant);

    $lineId = $delivery->lines->first()->id;
    $this->deliveries->confirm($delivery, null, [$lineId => 4]);

    $product->refresh();
    // 10 delivered minus 4 returned = 6 leave physically
    expect($product->stock)->toBe(44);
    expect($delivery->lines->first()->refresh()->quantite_rendue)->toBe(4);
});
