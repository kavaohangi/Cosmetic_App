<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProduitReapprovisionne;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role as SpatieRole;

function userWithRole(Role $role, array $attrs = []): User
{
    SpatieRole::findOrCreate($role->value, 'web');
    $user = User::factory()->create([...$attrs, 'role' => $role]);
    $user->assignRole($role->value);

    return $user;
}

it('notifies the chef marketing when a ruptured ordered product is replenished', function () {
    Notification::fake();

    $chef = userWithRole(Role::ChefMarketing, ['is_active' => true]);
    $magasinier = userWithRole(Role::Magasinier);

    $product = Product::factory()->create(['stock' => 0, 'seuil_alerte' => 5]);
    $client = Client::factory()->create();

    $order = Order::create([
        'reference' => 'CMD-'.uniqid(),
        'client_id' => $client->id,
        'user_id' => $magasinier->id,
        'statut' => OrderStatus::Validee,
        'total' => 0,
        'date_commande' => today(),
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'quantite' => 3,
        'prix_unitaire' => 10,
        'sous_total' => 30,
    ]);

    $this->actingAs($magasinier)
        ->patch(route('products.adjust', $product), ['stock' => 50, 'motif' => 'Production'])
        ->assertRedirect();

    expect($product->refresh()->stock)->toBe(50);

    Notification::assertSentTo($chef, ProduitReapprovisionne::class);
});

it('does not notify when product was not ordered', function () {
    Notification::fake();

    $chef = userWithRole(Role::ChefMarketing, ['is_active' => true]);
    $magasinier = userWithRole(Role::Magasinier);
    $product = Product::factory()->create(['stock' => 0, 'seuil_alerte' => 5]);

    $this->actingAs($magasinier)
        ->patch(route('products.adjust', $product), ['stock' => 50])
        ->assertRedirect();

    Notification::assertNothingSentTo($chef);
});
