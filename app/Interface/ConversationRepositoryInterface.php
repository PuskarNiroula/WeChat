<?php
namespace App\Interface;

interface ConversationRepositoryInterface
{

    public function createPrivateConversation();
    public function createGroupConversation(string $name);
}
