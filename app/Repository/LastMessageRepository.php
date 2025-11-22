<?php

namespace App\Repository;

use App\Interface\LastMessageRepositoryInterface;
use App\Models\LastMessage;


class LastMessageRepository implements LastMessageRepositoryInterface
{

    public function createLastMessage(int $conversationId, int $lastMessageId)
    {
        return LastMessage::create([
            'conversation_id'=>$conversationId,
            'message_id'=>$lastMessageId
        ]);
    }

    public function getLastMessage(int $conversationId)
    {
      return LastMessage::where('conversation_id',$conversationId)->firstOrFail();
    }

    public function updateLastMessage(int $conversationId, int $lastMessageId)
    {
      $lastMessage=$this->getLastMessage($conversationId);
        $lastMessage->message_id=$lastMessageId;
        $lastMessage->save();
        return $lastMessage;
    }

    public function checkIfLastMessageExist(int $conversationId): bool
    {
      if(LastMessage::where('conversation_id',$conversationId)->exists())
          return true;
      return false;
    }

    /**
     * @return mixed
     */
    public function getSidebar(array $conversationIds): mixed
    {
        return LastMessage::whereIn('conversation_id', $conversationIds)
            ->with(['message.user', 'message.conversation.conUsers'])
            ->orderByDesc('updated_at')->get();

    }
}
