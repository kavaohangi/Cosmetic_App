<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * A field marketer ("marketeur terrain") may only chat with:
     *  - their direct supervisor (the agent), or
     *  - colleagues sharing the same supervisor (same level).
     * Any other role may chat with anyone.
     */
    public function canChatWith(User $auth, User $receiver): bool
    {
        if ($auth->id === $receiver->id) {
            return false;
        }

        if ($auth->role === Role::MarketeurTerrain) {
            return $receiver->id === $auth->supervisor_id
                || ($auth->supervisor_id !== null && $receiver->supervisor_id === $auth->supervisor_id);
        }

        return true;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->id === $message->receiver_id;
    }

    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }
}
