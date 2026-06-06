<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RuptureStock extends Notification
{
    use Queueable;

    /**
     * @param  Collection<int, Product>  $produits
     */
    public function __construct(public Collection $produits) {}

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
            'type' => 'rupture_stock',
            'count' => $this->produits->count(),
            'produits' => $this->produits->map(fn (Product $p): array => [
                'id' => $p->id,
                'name' => $p->name,
                'stock' => $p->stock,
                'seuil_alerte' => $p->seuil_alerte,
            ])->all(),
            'message' => sprintf('%d produit(s) en rupture ou sous le seuil d\'alerte.', $this->produits->count()),
        ];
    }
}
