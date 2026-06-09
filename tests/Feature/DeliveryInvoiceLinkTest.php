<?php

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DeliveryService;
use App\Services\InventoryService;
use App\Services\InvoiceService;

beforeEach(function () {
    $this->inventory = app(InventoryService::class);
    $this->deliveries = app(DeliveryService::class);
    $this->invoices = app(InvoiceService::class);
});

function reservedOrder(Product $product, int $qty): Order
{
    $user = User::factory()->create(['name' => 'Jean Petit']);
    $client = Client::factory()->create(['name' => 'Boutique Alpha']);

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

it('binds an invoice with the chosen sale type when a delivery is emitted', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0, 'price' => 10]);
    $order = reservedOrder($product, 4);
    $this->inventory->reserveForOrder($order);

    $delivery = $this->deliveries->createFromOrder($order, SaleType::Credit);

    expect($delivery->invoice_id)->not->toBeNull();

    $invoice = $delivery->invoice;
    expect($invoice->type_vente)->toBe(SaleType::Credit);
    expect((float) $invoice->montant)->toBe(40.0);
    expect($invoice->statut)->toBe(InvoiceStatus::Emise);
});

it('shows payment progression on the invoice (partial then paid)', function () {
    $product = Product::factory()->create(['stock' => 50, 'stock_reserved' => 0, 'price' => 10]);
    $order = reservedOrder($product, 4);
    $this->inventory->reserveForOrder($order);
    $delivery = $this->deliveries->createFromOrder($order, SaleType::Credit);
    $invoice = $delivery->invoice;

    $this->invoices->registerPayment($invoice, 15);
    $invoice->refresh();
    expect($invoice->statut)->toBe(InvoiceStatus::Partielle);
    expect($invoice->resteAPayer())->toBe(25.0);

    $this->invoices->registerPayment($invoice, 25);
    $invoice->refresh();
    expect($invoice->statut)->toBe(InvoiceStatus::Payee);
    expect($invoice->resteAPayer())->toBe(0.0);
});
