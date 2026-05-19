<?php
namespace App\Repository;

use App\Interface\ConversationRepositoryInterface;
use App\Models\Conversation;

class ConversationRepository implements ConversationRepositoryInterface
{

    public function createPrivateConversation():Conversation
    {
       $conversation= Conversation::create([
            'type'=>'private'
        ]);
       return $conversation;
    }

    public function createGroupConversation(string $name)
    {
        $conversation = Conversation::create([
            'type' => 'group',
            'latest_key_version' => 1,
            'name' => $name,
        ]);
        return $conversation;
    }
}
