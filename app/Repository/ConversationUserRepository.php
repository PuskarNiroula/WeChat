<?php
namespace App\Repository;
use App\Interface\ConversationUserRepositoryInterface;
use App\Models\ConUser;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class ConversationUserRepository implements ConversationUserRepositoryInterface
{
    public function FindConversation(int $userId)
    {
        return ConUser::where('user_id',$userId)
             ->whereIn('conversation_id',function ($query) use ($userId) {
                 $query->select('conversation_id')
                     ->from('conversation_user')
                     ->where('user_id', auth()->id());
                     })
                     ->with('user')
                     ->first();
    }

    public function CreateConversation( int $conversationId,int $userId, int $myId): ConUser
    {
        DB::beginTransaction();
        ConUser::create([
            'user_id'=>$userId,
            'conversation_id'=>$conversationId
        ]);
        ConUser::create([
            'user_id'=>$myId,
            'conversation_id'=>$conversationId
        ]);
        DB::commit();
        return ConUser::where('conversation_id',$conversationId)->where('user_id',$userId)->with('user')->first();
    }

    public function getReceiverId(int $conversationId): int
    {
        return ConUser::where('conversation_id',$conversationId)->where('user_id','!=',auth()->id())->pluck('user_id')->first();
    }

    public function getConversationIdofUser(int $userId): array
    {
        return ConUser::where('user_id',$userId)->pluck('conversation_id')->toArray();
    }
}
