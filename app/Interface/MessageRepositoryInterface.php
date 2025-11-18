<?php
namespace App\Interface;

interface MessageRepositoryInterface
{
    public function createMessage(array $messageDto);
    public function getMessagesByConversation(int $conversationId, int $perPage);
    public function markAsRead(int $conversationId);

}
