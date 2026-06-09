<?php

namespace App\Notifications;

use App\Models\StockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlerteRuptureMagasinier extends Notification
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
            'type' => 'alerte_rupture_magasinier',
            'alert_id' => $this->alert->id,
            'product' => $this->alert->product?->name,
            'description' => $this->alert->description,
            'message' => sprintf('Le magasinier signale une rupture : %s.', $this->alert->product?->name ?? 'produit'),
        ];
    }
}
