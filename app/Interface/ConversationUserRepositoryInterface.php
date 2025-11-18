<?php

namespace App\Interface;

interface ConversationUserRepositoryInterface
{
    public function FindConversation(int $userId);
    public function CreateConversation( int $conversationId,int $userId,int $myId);
    public function getReceiverId(int $conversationId):int;

    public function getConversationIdofUser(int $userId):array;

}
