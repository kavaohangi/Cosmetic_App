<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\TerrainComplaint;
use App\Models\User;

class TerrainComplaintPolicy
{
    /**
     * Only field marketers (agents terrain) can create complaints/propositions.
     */
    public function create(User $user): bool
    {
        return $user->role === Role::MarketeurTerrain;
    }

    /**
     * Agent can view their own complaints.
     */
    public function viewOwn(User $user): bool
    {
        return $user->role === Role::MarketeurTerrain;
    }

    /**
     * Agent can view their own complaint details.
     */
    public function view(User $user, TerrainComplaint $complaint): bool
    {
        // Agent can view their own
        if ($complaint->user_id === $user->id) {
            return true;
        }

        // Marketing agent can view complaints from their team
        if ($user->role === Role::AgentMarketeur) {
            return $user->terrains()->where('id', $complaint->user_id)->exists();
        }

        // Admin and above can view all
        return in_array($user->role, [Role::Admin, Role::Directeur, Role::ChefMarketing], true);
    }

    /**
     * Marketing agent or above can view team complaints.
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

    /**
     * Marketing agent can review and respond to complaints.
     */
    public function review(User $user, TerrainComplaint $complaint): bool
    {
        if ($user->role === Role::AgentMarketeur) {
            return $user->terrains()->where('id', $complaint->user_id)->exists();
        }

        return in_array($user->role, [Role::Admin, Role::Directeur, Role::ChefMarketing], true);
    }

    /**
     * Only the agent who created it can delete.
     */
    public function delete(User $user, TerrainComplaint $complaint): bool
    {
        return $complaint->user_id === $user->id;
    }

    /**
     * Only the agent who created it can update.
     */
    public function update(User $user, TerrainComplaint $complaint): bool
    {
        return $complaint->user_id === $user->id;
    }
}
