<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommandeTraitee extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public string $action) {}

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
        $message = match ($this->action) {
            'validated' => sprintf(
                'Votre commande %s (%s) a été validée.',
                $this->order->reference,
                $this->order->client?->name ?? ''
            ),
            'rejected' => sprintf(
                'Votre commande %s (%s) a été refusée.',
                $this->order->reference,
                $this->order->client?->name ?? ''
            ),
            'stock_insufficient' => sprintf(
                'Votre commande %s (%s) est en attente : stock insuffisant. Les responsables ont été alertés.',
                $this->order->reference,
                $this->order->client?->name ?? ''
            ),
            default => sprintf('Votre commande %s a été mise à jour.', $this->order->reference),
        };

        return [
            'type' => 'commande_traitee',
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
            'action' => $this->action,
            'client_name' => $this->order->client?->name,
            'total' => (float) $this->order->total,
            'message' => $message,
        ];
    }
}
