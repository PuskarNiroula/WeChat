<?php

namespace App\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversation_id;
    public int $receiver_id;
    public string $message;
    public string $time;

    public function __construct($receiver_id,$conversation_id,$message,$time)
    {
      $this->receiver_id=$receiver_id;
      $this->conversation_id=$conversation_id;
      $this->message=$message;
      $this->time=$time;

        if (config('app.debug')) {
            Log::info('📡 MessageSent event triggered!',
                [   'id' => $receiver_id,
                    'conversation_id' => $conversation_id,
                    'message' => $message,
                    'time' => $time,
                ]);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {

            return new PrivateChannel("Message-Channel.{$this->receiver_id}");

    }
    public function broadcastAs():string{
        return 'message-sent';
    }
}
