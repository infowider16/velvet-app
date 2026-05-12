<?php
// app/Events/MessageReadEvent.php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $otherUserId;

    public function __construct($userId, $otherUserId)
    {
        $this->userId = $userId;
        $this->otherUserId = $otherUserId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->otherUserId);
    }

    public function broadcastAs()
    {
        return 'messages.read';
    }

    public function broadcastWith()
    {
        return [
            'read_by' => $this->userId,
            'conversation_with' => $this->otherUserId,
            'read_at' => now()->toIso8601String()
        ];
    }
}