<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Enums\StockMovementType;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Central inventory engine enforcing the "reserved then issued" flow.
 *
 * - Physical stock (`products.stock`) only changes on a real entry/issue.
 * - Reserved stock (`products.stock_reserved`) blocks availability without
 *   touching the physical quantity, until the goods are issued or released.
 * - Every change is journalled in `stock_movements`.
 */
class InventoryService
{
    /**
     * Reserve stock for every line of a validated order.
     * Throws when availability is insufficient for any line.
     *
     * @return array<int, array{product_id:int, name:string, demande:int, disponible:int}> Empty on success.
     */
    public function reserveForOrder(Order $order, ?int $userId = null): array
    {
        return DB::transaction(function () use ($order, $userId): array {
            $manquants = [];

            foreach ($order->items()->with('product')->get() as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if ($product === null) {
                    continue;
                }

                if ($product->disponible < $item->quantite) {
                    $manquants[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'demande' => $item->quantite,
                        'disponible' => $product->disponible,
                    ];
                }
            }

            if ($manquants !== []) {
                return $manquants;
            }

            foreach ($order->items()->with('product')->get() as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if ($product === null) {
                    continue;
                }

                $product->increment('stock_reserved', $item->quantite);

                InventoryReservation::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantite' => $item->quantite,
                    'statut' => ReservationStatus::Active,
                    'created_by' => $userId,
                ]);

                $this->log($product, StockMovementType::Reservation, $item->quantite, 'Réservation commande '.$order->reference, $order, $userId);
            }

            return [];
        });
    }

    /**
     * Release every active reservation of an order (e.g. on rejection/cancel).
     */
    public function releaseForOrder(Order $order, ?int $userId = null): void
    {
        DB::transaction(function () use ($order, $userId): void {
            $reservations = InventoryReservation::query()
                ->where('order_id', $order->id)
                ->active()
                ->get();

            foreach ($reservations as $reservation) {
                $product = Product::query()->lockForUpdate()->find($reservation->product_id);

                if ($product !== null) {
                    $product->decrement('stock_reserved', min($reservation->quantite, $product->stock_reserved));
                    $this->log($product, StockMovementType::Liberation, -$reservation->quantite, 'Libération commande '.$order->reference, $order, $userId);
                }

                $reservation->update(['statut' => ReservationStatus::Liberee]);
            }
        });
    }

    /**
     * Physically issue a quantity (goods issue / delivery). Consumes the matching
     * reservation when present and decrements physical stock. This is the ONLY
     * way physical stock leaves the warehouse.
     */
    public function issue(Product $product, int $quantite, ?Model $source = null, ?int $userId = null, ?string $motif = null): void
    {
        if ($quantite <= 0) {
            throw new RuntimeException('La quantité à sortir doit être positive.');
        }

        DB::transaction(function () use ($product, $quantite, $source, $userId, $motif): void {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);

            if ($locked->stock < $quantite) {
                throw new RuntimeException("Stock physique insuffisant pour {$locked->name}.");
            }

            $locked->decrement('stock', $quantite);

            if ($locked->stock_reserved > 0) {
                $locked->decrement('stock_reserved', min($quantite, $locked->stock_reserved));
            }

            $this->log($locked->refresh(), StockMovementType::Sortie, -$quantite, $motif ?? 'Bon de sortie', $source, $userId);
        });
    }

    /**
     * Register a physical entry (production / reception).
     */
    public function entry(Product $product, int $quantite, ?int $userId = null, ?string $motif = null): void
    {
        if ($quantite <= 0) {
            throw new RuntimeException('La quantité à entrer doit être positive.');
        }

        DB::transaction(function () use ($product, $quantite, $userId, $motif): void {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $locked->increment('stock', $quantite);
            $this->log($locked->refresh(), StockMovementType::Entree, $quantite, $motif ?? 'Entrée stock', null, $userId);
        });
    }

    /**
     * Adjust physical stock to an absolute target value (inventory correction).
     */
    public function adjust(Product $product, int $nouveauStock, ?int $userId = null, ?string $motif = null): void
    {
        DB::transaction(function () use ($product, $nouveauStock, $userId, $motif): void {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $delta = $nouveauStock - $locked->stock;

            if ($delta === 0) {
                return;
            }

            $locked->update(['stock' => max(0, $nouveauStock)]);
            $this->log($locked->refresh(), StockMovementType::Ajustement, $delta, $motif ?? 'Ajustement inventaire', null, $userId);
        });
    }

    private function log(Product $product, StockMovementType $type, int $quantite, string $motif, ?Model $source, ?int $userId): void
    {
        StockMovement::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantite' => $quantite,
            'stock_apres' => $product->stock,
            'motif' => $motif,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'user_id' => $userId,
        ]);
    }
}
