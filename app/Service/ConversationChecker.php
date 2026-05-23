<?php

namespace App\Service;

use App\Enums\ConversationUserStatus;
use App\Models\ConUser;

class ConversationChecker
{
    public function IsUserInConversation($conversationId,$userId):bool{
        return ConUser::where('conversation_id',$conversationId)->where('user_id',$userId)->exists();
    }
    public function IsUserAdminInConversation($conversationId,$userId):bool{
        return ConUser::where('conversation_id',$conversationId)->where('user_id',$userId)
            ->where('status',ConversationUserStatus::ACTIVE)->where('is_admin',1)->exists();

    }

}
