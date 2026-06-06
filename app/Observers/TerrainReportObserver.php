<?php

namespace App\Observers;

use App\Models\TerrainReport;
use App\Notifications\RapportSoumis;
use Illuminate\Support\Facades\Notification;

class TerrainReportObserver
{
    /**
     * Notify the supervisor when a terrain report is submitted.
     */
    public function created(TerrainReport $report): void
    {
        $report->loadMissing('supervisor', 'user');

        if ($report->supervisor !== null) {
            Notification::send($report->supervisor, new RapportSoumis($report));
        }
    }
}
