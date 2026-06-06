<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($order->user_id === $user->id) {
            return true;
        }

        return in_array($user->role, [
            Role::Admin,
            Role::Directeur,
            Role::ChefMarketing,
            Role::Commercial,
            Role::Magasinier,
        ], true);
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

    public function update(User $user, Order $order): bool
    {
        if (in_array($user->role, [Role::Admin, Role::Directeur, Role::Commercial], true)) {
            return true;
        }

        return $order->user_id === $user->id && $order->statut === OrderStatus::EnAttente;
    }

    /**
     * Validating an order (stock impact) is reserved to staff roles.
     */
    public function validate(User $user, Order $order): bool
    {
        // Seul le Chef Marketing valide ; l'Admin garde un accès de secours.
        return in_array($user->role, [
            Role::ChefMarketing,
            Role::Admin,
        ], true);
    }

    /**
     * Refusing a pending order.
     */
    public function reject(User $user, Order $order): bool
    {
        return $this->validate($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return in_array($user->role, [Role::Admin, Role::Directeur], true);
    }
}
