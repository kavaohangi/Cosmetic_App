<?php

use App\Enums\Role;
use App\Models\Client;
use App\Models\Product;
use App\Models\TerrainReport;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

function terrainUser(): User
{
    SpatieRole::findOrCreate(Role::MarketeurTerrain->value, 'web');
    $user = User::factory()->create(['role' => Role::MarketeurTerrain, 'magasin' => 'Boutique Centre']);
    $user->assignRole(Role::MarketeurTerrain->value);

    return $user;
}

it('stores a terrain report with each line attributed to a client', function () {
    $agent = terrainUser();
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $product = Product::factory()->create(['price' => 10]);

    $this->actingAs($agent)
        ->post(route('terrain.store'), [
            'date' => today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'client_id' => $clientA->id, 'quantite' => 2, 'prix_unitaire' => 10],
                ['product_id' => $product->id, 'client_id' => $clientB->id, 'quantite' => 3, 'prix_unitaire' => 10],
                ['product_id' => $product->id, 'quantite' => 1, 'prix_unitaire' => 10],
            ],
        ])
        ->assertRedirect();

    $report = TerrainReport::first();
    expect($report->items)->toHaveCount(3);
    expect($report->nb_ventes)->toBe(6);

    $byClient = $report->items->groupBy('client_id');
    expect($byClient->get($clientA->id))->toHaveCount(1);
    expect($byClient->get($clientB->id))->toHaveCount(1);
    expect($byClient->get(null) ?? $byClient->get(''))->toHaveCount(1);
});
