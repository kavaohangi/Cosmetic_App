<?php

namespace App\Services;

use App\Models\TerrainReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TerrainService
{
    /**
     * All terrain reports submitted by the team of a given agent (supervisor).
     *
     * @return Collection<int, TerrainReport>
     */
    public function getMyTeamReports(int $agentId): Collection
    {
        return TerrainReport::query()
            ->where('supervisor_id', $agentId)
            ->with(['user', 'supervisor'])
            ->latest('date')
            ->get();
    }

    /**
     * Colleagues of a field marketer (same supervisor, excluding self).
     *
     * @return Collection<int, User>
     */
    public function getColleagues(int $terrainId): Collection
    {
        $terrain = User::findOrFail($terrainId);

        return $terrain->colleagues()->where('is_active', true)->get();
    }
}
