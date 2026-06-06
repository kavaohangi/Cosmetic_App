<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\StockAlertStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\RuptureStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class OrderService
{
    /**
     * Check stock availability for every line of the order.
     *
     * @return array<int, array{product_id:int, name:string, demande:int, disponible:int}>
     *                                                                                  The list of products with insufficient stock (empty when the order is servable).
     */
    public function checkStock(Order $order): array
    {
        $manquants = [];

        foreach ($order->items()->with('product')->get() as $item) {
            $product = $item->product;

            if ($product === null) {
                continue;
            }

            if ($product->stock < $item->quantite) {
                $manquants[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'demande' => $item->quantite,
                    'disponible' => $product->stock,
                ];
            }
        }

        return $manquants;
    }

    /**
     * Persist a stock alert per missing product so they can be filtered later.
     * Avoids duplicating an alert that is still pending for the same product/order.
     *
     * @param  array<int, array{product_id:int, name:string, demande:int, disponible:int}>  $manquants
     */
    public function recordStockAlerts(?Order $order, array $manquants, ?int $createdBy = null): void
    {
        foreach ($manquants as $manque) {
            $exists = StockAlert::query()
                ->where('product_id', $manque['product_id'])
                ->where('order_id', $order?->id)
                ->where('statut', StockAlertStatus::EnAttente->value)
                ->exists();

            if ($exists) {
                continue;
            }

            StockAlert::create([
                'product_id' => $manque['product_id'],
                'order_id' => $order?->id,
                'created_by' => $createdBy,
                'quantite_demandee' => $manque['demande'],
                'quantite_disponible' => $manque['disponible'],
                'statut' => StockAlertStatus::EnAttente,
            ]);
        }
    }

    /**
     * Remove from the order ("panier") every line whose product can no longer
     * be served, recompute the total, and return the removed lines so the caller
     * can raise alerts/notifications. The alerting system stays in place.
     *
     * @return array<int, array{product_id:int, name:string, demande:int, disponible:int}>
     */
    public function pruneUnavailableItems(Order $order): array
    {
        $manquants = [];
        $total = 0;

        foreach ($order->items()->with('product')->get() as $item) {
            $product = $item->product;

            if ($product === null) {
                $item->delete();
                continue;
            }

            if ($product->stock < $item->quantite) {
                $manquants[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'demande' => $item->quantite,
                    'disponible' => $product->stock,
                ];
                $item->delete();
                continue;
            }

            $total += (float) $item->sous_total;
        }

        $order->update(['total' => $total]);
        $order->setRelation('items', $order->items()->with('product')->get());

        return $manquants;
    }

    /**
     * Notify the stock managers about every product currently in rupture.
     */
    public function notifyRupture(): void
    {
        $produitsEnRupture = Product::query()->enRupture()->get();

        if ($produitsEnRupture->isEmpty()) {
            return;
        }

        $destinataires = $this->stockManagers();

        if ($destinataires->isEmpty()) {
            return;
        }

        Notification::send($destinataires, new RuptureStock($produitsEnRupture));
    }

    /**
     * @return Collection<int, User>
     */
    protected function stockManagers(): Collection
    {
        return User::query()
            ->whereIn('role', [Role::Magasinier->value, Role::Directeur->value, Role::Admin->value])
            ->where('is_active', true)
            ->get();
    }
}
