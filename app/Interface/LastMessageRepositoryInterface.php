<?php
namespace App\Interface;

interface LastMessageRepositoryInterface
{
    public function createLastMessage(int $conversationId,int $lastMessageId);
    public function getLastMessage(int $conversationId);
    public function updateLastMessage(int $conversationId,int $lastMessageId);

}
