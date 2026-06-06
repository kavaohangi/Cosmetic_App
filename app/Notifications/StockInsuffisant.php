<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockInsuffisant extends Notification
{
    use Queueable;

    /**
     * @param array<int, array{product_id:int, name:string, demande:int, disponible:int}> $manquants
     */
    public function __construct(public Order $order, public array $manquants) {}

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
        $produits = collect($this->manquants)->pluck('name')->implode(', ');

        return [
            'type' => 'stock_insuffisant',
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
            'client_name' => $this->order->client?->name,
            'produits' => $this->manquants,
            'message' => sprintf(
                'Stock insuffisant pour la commande %s (%s). Produits manquants : %s',
                $this->order->reference,
                $this->order->client?->name ?? '',
                $produits
            ),
        ];
    }
}
