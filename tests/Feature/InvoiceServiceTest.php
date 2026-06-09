<?php

use App\Enums\InvoiceStatus;
use App\Enums\SaleType;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;

beforeEach(function () {
    $this->service = app(InvoiceService::class);
});

it('creates an invoice with computed amount', function () {
    $agent = User::factory()->create();
    $client = Client::factory()->create(['agent_id' => $agent->id]);
    $p1 = Product::factory()->create(['price' => 10]);
    $p2 = Product::factory()->create(['price' => 5]);

    $invoice = $this->service->create($client->id, $agent->id, SaleType::Credit, [
        ['product_id' => $p1->id, 'quantite' => 2],
        ['product_id' => $p2->id, 'quantite' => 4],
    ]);

    expect((float) $invoice->montant)->toBe(40.0);
    expect($invoice->lines)->toHaveCount(2);
    expect($invoice->statut)->toBe(InvoiceStatus::Emise);
});

it('updates status on partial and full payment', function () {
    $agent = User::factory()->create();
    $client = Client::factory()->create(['agent_id' => $agent->id]);
    $product = Product::factory()->create(['price' => 50]);

    $invoice = $this->service->create($client->id, $agent->id, SaleType::Credit, [
        ['product_id' => $product->id, 'quantite' => 2],
    ]);

    $this->service->registerPayment($invoice, 40);
    expect($invoice->refresh()->statut)->toBe(InvoiceStatus::Partielle);

    $this->service->registerPayment($invoice, 60);
    $invoice->refresh();
    expect($invoice->statut)->toBe(InvoiceStatus::Payee);
    expect((float) $invoice->montant_paye)->toBe(100.0);
});

it('flags clients with outstanding credit', function () {
    $agent = User::factory()->create();
    $client = Client::factory()->create(['agent_id' => $agent->id]);
    $product = Product::factory()->create(['price' => 50]);

    $this->service->create($client->id, $agent->id, SaleType::Credit, [
        ['product_id' => $product->id, 'quantite' => 1],
    ]);

    expect(Client::avecCredit()->pluck('id'))->toContain($client->id);
});
