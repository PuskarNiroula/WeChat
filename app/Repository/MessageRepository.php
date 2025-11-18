<?php
namespace App\Repository;

use App\Interface\MessageRepositoryInterface;
use App\Models\Message;

class MessageRepository implements MessageRepositoryInterface
{
    public function createMessage(array $messageDto)
    {
        Message::create([
            'conversation_id'=>$messageDto['conversation_id'],
            'sender_id'=>$messageDto['sender_id'],
            'message'=>$messageDto['message']
        ]);

    }
}
