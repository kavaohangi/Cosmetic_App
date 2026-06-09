<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Delivery;
use App\Models\GoodsIssueNote;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orchestrates deliveries (bons de livraison) and their backing goods issue
 * notes (bons de sortie). Physical stock only leaves through this service,
 * always justified by a delivery + goods issue note pair.
 */
class DeliveryService
{
    public function __construct(
        private InventoryService $inventory,
        private DocumentNumberService $numbers,
        private InvoiceService $invoices,
    ) {}

    /**
     * Create a "prepared" delivery from a validated (reserved) order.
     */
    public function createFromOrder(Order $order, SaleType $typeVente, ?int $agentId = null, ?int $createdBy = null): Delivery
    {
        if ($order->statut !== OrderStatus::Validee && $order->statut !== OrderStatus::EnPreparation) {
            throw new RuntimeException('Seule une commande validée peut être livrée.');
        }

        return DB::transaction(function () use ($order, $typeVente, $agentId, $createdBy): Delivery {
            $orderItems = $order->items()->with('product')->get();

            // The payment mode (credit/cash) chosen when emitting the goods issue
            // is bound to an invoice so the Chef Marketing can track its progress.
            $invoice = $this->invoices->create(
                clientId: $order->client_id,
                agentId: $agentId ?? $order->user_id,
                typeVente: $typeVente,
                items: $orderItems->map(fn ($item): array => [
                    'product_id' => $item->product_id,
                    'quantite' => (int) $item->quantite,
                ])->all(),
                orderId: $order->id,
            );

            $delivery = Delivery::create([
                'reference' => 'BL-'.strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'agent_id' => $agentId ?? $order->user_id,
                'created_by' => $createdBy,
                'invoice_id' => $invoice->id,
                'statut' => DeliveryStatus::Prepare,
                'type_vente' => $typeVente,
                'date' => today(),
            ]);

            foreach ($orderItems as $item) {
                $delivery->lines()->create([
                    'product_id' => $item->product_id,
                    'quantite' => $item->quantite,
                    'quantite_rendue' => 0,
                    'prix_unitaire' => $item->prix_unitaire,
                ]);
            }

            $order->update(['statut' => OrderStatus::EnPreparation]);

            return $delivery;
        });
    }

    /**
     * Confirm a delivery: physically issue the net quantity for each line,
     * generate the backing goods issue note, and mark the order delivered.
     *
     * @param  array<int, int>  $returns  Map of delivery_line_id => quantite_rendue.
     */
    public function confirm(Delivery $delivery, ?int $userId = null, array $returns = []): GoodsIssueNote
    {
        if ($delivery->statut === DeliveryStatus::Livre) {
            throw new RuntimeException('Cette livraison a déjà été confirmée.');
        }

        return DB::transaction(function () use ($delivery, $userId, $returns): GoodsIssueNote {
            $delivery->loadMissing(['agent', 'client']);

            $reference = $this->numbers->goodsIssueReference(
                agentName: $delivery->agent?->name ?? 'XX',
                clientName: $delivery->client?->name ?? 'XX',
            );

            $note = GoodsIssueNote::create([
                'reference' => $reference,
                'delivery_id' => $delivery->id,
                'issued_by' => $userId,
                'date' => today(),
                'motif' => 'Livraison '.$delivery->reference,
            ]);

            foreach ($delivery->lines()->with('product')->get() as $line) {
                $rendue = max(0, min($line->quantite, (int) ($returns[$line->id] ?? 0)));

                if ($rendue !== (int) $line->quantite_rendue) {
                    $line->update(['quantite_rendue' => $rendue]);
                }

                $nette = max(0, (int) $line->quantite - $rendue);

                if ($nette > 0 && $line->product !== null) {
                    $this->inventory->issue($line->product, $nette, $note, $userId, 'Bon de sortie '.$note->reference);

                    $note->lines()->create([
                        'product_id' => $line->product_id,
                        'quantite' => $nette,
                    ]);
                }
            }

            $delivery->update([
                'statut' => DeliveryStatus::Livre,
                'delivered_by' => $userId,
                'delivered_at' => now(),
            ]);

            $delivery->order?->update(['statut' => OrderStatus::Livree]);

            return $note;
        });
    }
}
