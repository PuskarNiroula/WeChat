<?php

namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversation_id;
    public int $receiver_id;

    public function __construct($receiver_id,$conversation_id)
    {
      $this->receiver_id=$receiver_id;
      $this->conversation_id=$conversation_id;

        if (config('app.debug')) {
            Log::info('📡 MessageSent event triggered!', ['id' => $receiver_id, 'conversation_id' => $conversation_id]);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): PrivateChannel
    {

            return new PrivateChannel("Message-Channel.{$this->receiver_id}");

    }
    public function broadcastAs():string{
        return 'message-sent';
    }
}
