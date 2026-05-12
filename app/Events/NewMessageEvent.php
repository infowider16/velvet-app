<?php
// app/Events/NewMessageEvent.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $senderId;
    public $receiverId;
    public $chatId;

    public function __construct($message, $senderId, $receiverId, $chatId = null)
    {
        $this->message = $message;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->chatId = $chatId;
    }

    public function broadcastOn()
    {
        // Broadcast to both sender and receiver
        return [
            new PrivateChannel('user.' . $this->senderId),
            new PrivateChannel('user.' . $this->receiverId)
        ];
    }

    public function broadcastAs()
    {
        return 'new.message';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'type' => 'individual',
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'timestamp' => now()->toIso8601String()
        ];
    }
}