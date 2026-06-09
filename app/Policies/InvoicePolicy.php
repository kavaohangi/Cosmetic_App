<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            Role::Admin,
            Role::Directeur,
            Role::ChefMarketing,
            Role::AgentMarketeur,
        ], true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === Role::AgentMarketeur) {
            return $invoice->agent_id === $user->id;
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role === Role::AgentMarketeur;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->role === Role::AgentMarketeur && $invoice->agent_id === $user->id;
    }
}
