<?php

namespace App\Notifications;

use App\Models\TerrainReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RapportSoumis extends Notification
{
    use Queueable;

    public function __construct(public TerrainReport $report) {}

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
            'type' => 'rapport_soumis',
            'report_id' => $this->report->id,
            'agent_id' => $this->report->user_id,
            'agent_name' => $this->report->user?->name,
            'date' => $this->report->date?->toDateString(),
            'nb_ventes' => $this->report->nb_ventes,
            'rupture_stock' => $this->report->rupture_stock,
            'message' => sprintf(
                '%s a soumis un rapport terrain (%d ventes).',
                $this->report->user?->name ?? 'Un marketeur',
                $this->report->nb_ventes
            ),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau rapport terrain')
            ->line(sprintf(
                '%s a soumis un rapport terrain avec %d ventes.',
                $this->report->user?->name ?? 'Un marketeur',
                $this->report->nb_ventes
            ))
            ->action('Consulter le rapport', url('/terrain/'.$this->report->id));
    }
}
