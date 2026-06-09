<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\SaleType;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Create an invoice for a client with the given product lines.
     *
     * @param  array<int, array{product_id:int, quantite:int}>  $items
     */
    public function create(int $clientId, int $agentId, SaleType $typeVente, array $items, ?string $notes = null, ?int $orderId = null): Invoice
    {
        return DB::transaction(function () use ($clientId, $agentId, $typeVente, $items, $notes, $orderId): Invoice {
            $invoice = Invoice::create([
                'reference' => 'FAC-'.strtoupper(Str::random(8)),
                'client_id' => $clientId,
                'agent_id' => $agentId,
                'order_id' => $orderId,
                'statut' => InvoiceStatus::Emise,
                'type_vente' => $typeVente,
                'montant' => 0,
                'montant_paye' => 0,
                'date' => today(),
                'notes' => $notes,
            ]);

            $montant = 0.0;

            foreach ($items as $line) {
                $product = Product::findOrFail($line['product_id']);
                $sousTotal = (float) $product->price * (int) $line['quantite'];
                $montant += $sousTotal;

                $invoice->lines()->create([
                    'product_id' => $product->id,
                    'quantite' => $line['quantite'],
                    'prix_unitaire' => $product->price,
                    'sous_total' => $sousTotal,
                ]);
            }

            $invoice->update(['montant' => $montant]);

            return $invoice;
        });
    }

    /**
     * Register a payment and recompute the invoice status.
     */
    public function registerPayment(Invoice $invoice, float $montant): Invoice
    {
        $paye = min((float) $invoice->montant, (float) $invoice->montant_paye + max(0, $montant));

        $statut = match (true) {
            $paye >= (float) $invoice->montant => InvoiceStatus::Payee,
            $paye > 0 => InvoiceStatus::Partielle,
            default => $invoice->statut,
        };

        $invoice->update([
            'montant_paye' => $paye,
            'statut' => $statut,
        ]);

        return $invoice;
    }
}
