<?php
namespace App\Service;

use App\Interface\ConversationRepositoryInterface;
use App\Interface\ConversationUserRepositoryInterface;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\ConversationUserRepository;
use Illuminate\Support\Facades\DB;

class ConversationService{

    protected ConversationRepositoryInterface $conversationRepository;
    protected ConversationUserRepositoryInterface $conversationUserRepository;

    public function __construct()
    {
        $this->conversationRepository=new ConversationRepository();
        $this->conversationUserRepository=new ConversationUserRepository();
    }

    public function createPrivateConversation($list):?int{
        DB::beginTransaction();
        $conversationId= $this->conversationRepository->createPrivateConversation();
        $this->conversationUserRepository->CreatePrivateConversation($conversationId,$list);
        DB::commit();
        return $conversationId;
    }
    public function checkIfPrivateConversationExist(int $receiverId, int $senderId)
    {
        $conversation = Conversation::where('type', 'private')
            ->whereHas('conUsers', function ($query) use ($senderId) {
                $query->where('user_id', $senderId);
            })
            ->whereHas('conUsers', function ($query) use ($receiverId) {
                $query->where('user_id', $receiverId);
            })
            ->select('id')
            ->first();

        return $conversation ? $conversation->id : null;
    }

}
