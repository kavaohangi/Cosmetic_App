<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the Chef Marketing when a previously out-of-stock product that had
 * pending client orders is replenished, so they can relay availability to agents.
 */
class ProduitReapprovisionne extends Notification
{
    use Queueable;

    public function __construct(public Product $produit, public int $nouveauStock) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'produit_reapprovisionne',
            'product_id' => $this->produit->id,
            'product' => $this->produit->name,
            'stock' => $this->nouveauStock,
            'message' => sprintf('%s est réapprovisionné (stock %d). Informez les agents pour les clients en attente.', $this->produit->name, $this->nouveauStock),
        ];
    }
}
