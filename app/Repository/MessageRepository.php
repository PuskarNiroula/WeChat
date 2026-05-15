<?php
namespace App\Repository;

use App\Enums\MessageTypeEnum;
use App\Interface\MessageRepositoryInterface;
use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository implements MessageRepositoryInterface
{
    public function createMessage(array $messageDto)
    {
       return Message::create([
            'conversation_id'=>$messageDto['conversation_id'],
            'sender_id'=>$messageDto['sender_id'],
            'message_type'=>MessageTypeEnum::TEXT,
            'encrypted_message'=>$messageDto['message'],
           'iv'=>$messageDto['iv'],
        ]);

    }
    public function getMessagesByConversation(int $conversationId, int $perPage = 50): LengthAwarePaginator
    {
        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark messages as read for a conversation (excluding sender).
     */
    public function markAsRead(int $conversationId): void
    {
        Message::where('conversation_id', $conversationId)
            ->whereNot('sender_id','!=',auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }
}
