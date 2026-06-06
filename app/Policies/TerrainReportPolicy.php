<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\TerrainReport;
use App\Models\User;

class TerrainReportPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            Role::Admin,
            Role::Directeur,
            Role::ChefMarketing,
            Role::AgentMarketeur,
            Role::MarketeurTerrain,
        ], true);
    }

    public function view(User $user, TerrainReport $report): bool
    {
        if ($report->user_id === $user->id) {
            return true;
        }

        if ($report->supervisor_id === $user->id) {
            return true;
        }

        return in_array($user->role, [Role::Admin, Role::Directeur, Role::ChefMarketing], true);
    }

    /**
     * Only field marketers create terrain reports.
     */
    public function create(User $user): bool
    {
        return $user->role === Role::MarketeurTerrain;
    }

    public function update(User $user, TerrainReport $report): bool
    {
        return $report->user_id === $user->id;
    }

    public function delete(User $user, TerrainReport $report): bool
    {
        return $report->user_id === $user->id
            || in_array($user->role, [Role::Admin, Role::Directeur], true);
    }

    /**
     * An agent (or above) may consult the consolidated team reports.
     */
    public function viewTeam(User $user): bool
    {
        return in_array($user->role, [
            Role::Admin,
            Role::Directeur,
            Role::ChefMarketing,
            Role::AgentMarketeur,
        ], true);
    }
}
