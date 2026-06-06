<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RapportTerrainSoumis extends Notification
{
    use Queueable;

    public function __construct(
        public User $agent,
        public string $periodeLabel,
        public float $totalCa,
        public int $totalUnites,
        public int $nbRapports,
    ) {}

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
            'type' => 'rapport_terrain_soumis',
            'agent_id' => $this->agent->id,
            'agent_name' => $this->agent->name,
            'periode' => $this->periodeLabel,
            'total_ca' => $this->totalCa,
            'total_unites' => $this->totalUnites,
            'nb_rapports' => $this->nbRapports,
            'message' => sprintf(
                '%s a soumis son rapport terrain (%s) : %s FCFA · %d unités · %d rapport(s).',
                $this->agent->name,
                $this->periodeLabel,
                number_format($this->totalCa, 0, ',', ' '),
                $this->totalUnites,
                $this->nbRapports,
            ),
        ];
    }
}
