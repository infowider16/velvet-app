<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class ChatSocketService
{
    protected ?Pusher $pusher = null;

    public function __construct()
    {
        try {
            $this->pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Pusher initialization failed', [
                'error' => $e->getMessage(),
            ]);

            $this->pusher = null;
        }
    }

    public function trigger(string $channel, string $event, array $payload = []): bool
    {
        try {
            if (!$this->pusher) {
                return false;
            }

            $this->pusher->trigger($channel, $event, $payload);

            return true;
        } catch (\Throwable $e) {
            Log::error('Pusher trigger failed', [
                'channel' => $channel,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}