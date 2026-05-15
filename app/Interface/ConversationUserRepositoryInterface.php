<?php

namespace App\Interface;


interface ConversationUserRepositoryInterface
{
    public function FindConversation(int $userId);
    public function createPrivateConversation($list,$conversationId):void;
    public function getReceiverId(int $conversationId):int;

    public function getConversationIdofUser(int $userId):array;
    public function checkValidUser(int $userId,int $conversationId):bool;

}
