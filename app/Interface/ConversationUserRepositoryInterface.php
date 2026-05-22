<?php

namespace App\Interface;


use App\Dto\ChatMember;

interface ConversationUserRepositoryInterface
{
    public function FindConversation(int $userId);
    public function createPrivateConversation($list,$conversationId):void;
    public function getReceiverId(int $conversationId):int;

    public function getConversationIdOfUser(int $userId):array;
    public function checkValidUser(int $userId,int $conversationId):bool;
    public function addMemberToConversation(int $conversationId, array $member, int $keyVersion):void;
    public function deactivateMemberId(int $userId,int $groupId);
    public function activateMemberId(int $userId,int $groupId);

}
