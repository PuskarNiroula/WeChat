<?php
namespace App\Repository;

use App\Interface\ConversationRepositoryInterface;
use App\Models\Conversation;

class ConversationRepository implements ConversationRepositoryInterface
{

    public function createPrivateConversation():Conversation
    {
        return Conversation::create([
             'type'=>'private',
            'latest_key_version'=>1,
         ]);
    }

    public function createGroupConversation(string $name)
    {
        return Conversation::create([
            'type' => 'group',
            'latest_key_version' => 1,
            'name' => $name,
        ]);
    }



}
