<?php

namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public int $sender_id;
    public int $receiver_id;

    public function __construct($message, $sender_id, $receiver_id)
    {
        $this->message = $message;
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        if (config('app.debug')) {
            Log::info('📡 MessageSent event triggered!', ['message' => $message]);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('chat-channel'),
        ];
    }
    public function broadcastAs():string{
        return 'message.sent';
    }
}
