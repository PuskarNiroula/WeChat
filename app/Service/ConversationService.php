<?php
namespace App\Service;

use App\Interface\ConversationRepositoryInterface;

class ConversationService{

    protected ConversationRepositoryInterface $conversationRepository;

    public function __construct(ConversationRepositoryInterface $conversationRepository)
    {
        $this->conversationRepository=$conversationRepository;
    }

    public function createPrivateConversation(){
        return $this->conversationRepository->createPrivateConversation();
    }

}
