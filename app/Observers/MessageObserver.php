<?php

namespace App\Observers;

use App\Events\MessageEnvoye;
use App\Models\Message;

class MessageObserver
{
    /**
     * Broadcast the message to the receiver in real time.
     */
    public function created(Message $message): void
    {
        broadcast(new MessageEnvoye($message));
    }
}
