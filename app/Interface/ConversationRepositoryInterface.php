<?php
namespace App\Interface;

use App\Dto\ChatMember;

interface ConversationRepositoryInterface
{

    public function createPrivateConversation();
    public function createGroupConversation(string $name);

}
