<?php

namespace App\Service;

use App\Interface\ConversationRepositoryInterface;
use App\Interface\ConversationUserRepositoryInterface;
use App\Repository\ConversationRepository;
use App\Repository\ConversationUserRepository;

class ConversationUserService
{
    protected ConversationUserRepositoryInterface $conversationUserRepository;
    protected ConversationRepositoryInterface $conversationRepository;
    public function __construct(){
        $this->conversationUserRepository=new ConversationUserRepository();
        $this->conversationRepository=new ConversationRepository();
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

    public function getConversationIdOfUser(int $userId):array{
       return $this->conversationUserRepository->getConversationIdofUser($userId);
    }
    public function checkValidConversation(int $userId, int $conversationId): bool
    {
        return $this->conversationUserRepository->checkValidUser($userId,$conversationId);

    }


}
