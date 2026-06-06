<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Client $client): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            Role::Admin,
            Role::Directeur,
            Role::Commercial,
            Role::AgentMarketeur,
            Role::MarketeurTerrain,
        ], true);
    }

    public function update(User $user, Client $client): bool
    {
        if (in_array($user->role, [Role::Admin, Role::Directeur, Role::Commercial], true)) {
            return true;
        }

        return $client->created_by === $user->id;
    }

    public function delete(User $user, Client $client): bool
    {
        return in_array($user->role, [Role::Admin, Role::Directeur], true);
    }
}
