<?php

namespace App\Service;

use App\Interface\ConversationRepositoryInterface;
use App\Interface\ConversationUserRepositoryInterface;

class ConversationUserService
{
    protected ConversationUserRepositoryInterface $conversationUserRepository;
    protected ConversationRepositoryInterface $conversationRepository;
    public function __construct(ConversationUserRepositoryInterface $conversationUserRepository,ConversationRepositoryInterface $conversationRepository){
        $this->conversationUserRepository=$conversationUserRepository;
        $this->conversationRepository=$conversationRepository;
    }

    /**
     * @throws \Exception
     */
    public function FindOrCreateConversation(int $userId){
        if (auth()->id() == $userId) {
            throw new \Exception("You can't send message to yourself");
        }
        $conversationWithUser= $this->conversationUserRepository->FindConversation($userId);
        if($conversationWithUser!=null){
            return $conversationWithUser;
        }
        $conversationId=$this->conversationRepository->createPrivateConversation();
        return $this->conversationUserRepository->CreateConversation($conversationId,$userId,auth()->id());
    }


}
