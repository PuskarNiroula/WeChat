<?php
namespace App\Repository;

use App\Interface\ConversationRepositoryInterface;
use App\Models\Conversation;

class ConversationRepository implements ConversationRepositoryInterface
{

    public function createPrivateConversation():int
    {
       $conversation= Conversation::create([
            'type'=>'private'
        ]);
       return $conversation->id;
    }
}
