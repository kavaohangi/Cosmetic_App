<?php

namespace App\Notifications;

use App\Models\StockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProduitDisponible extends Notification
{
    use Queueable;

    public function __construct(public StockAlert $alert) {}

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
            'type' => 'produit_disponible',
            'alert_id' => $this->alert->id,
            'product_id' => $this->alert->product_id,
            'product_name' => $this->alert->product?->name,
            'order_id' => $this->alert->order_id,
            'resolved_by' => $this->alert->resolver?->name,
            'message' => sprintf(
                'Le produit "%s" est de nouveau disponible. Vous pouvez relancer vos commandes.',
                $this->alert->product?->name ?? 'concerné'
            ),
        ];
    }
}
