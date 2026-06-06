<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommandeSoumise extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

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
            'type' => 'commande_soumise',
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
            'agent_id' => $this->order->user_id,
            'agent_name' => $this->order->user?->name,
            'client_name' => $this->order->client?->name,
            'total' => (float) $this->order->total,
            'message' => sprintf(
                '%s a soumis une commande de %s FCFA pour le client %s.',
                $this->order->user?->name ?? 'Un agent',
                number_format((float) $this->order->total, 0, ',', ' '),
                $this->order->client?->name ?? 'Inconnu'
            ),
        ];
    }
}
