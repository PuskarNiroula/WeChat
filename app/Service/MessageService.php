<?php
namespace App\Service;

use App\Events\MessageSent;
use App\Interface\ConversationUserRepositoryInterface;
use App\Interface\LastMessageRepositoryInterface;
use App\Interface\MessageRepositoryInterface;
use App\Repository\ConversationUserRepository;
use App\Repository\LastMessageRepository;
use App\Repository\MessageRepository;

class MessageService {
    protected MessageRepositoryInterface $messageRepository;
    protected LastMessageRepositoryInterface $lastMessageRepository;
    protected ConversationUserRepositoryInterface $conversationUserRepository;

    public function __construct(){
        $this->messageRepository=new MessageRepository();
        $this->lastMessageRepository=new LastMessageRepository();
        $this->conversationUserRepository=new ConversationUserRepository();
    }

    public function createMessage(array $messageDto){
        $this->messageRepository->createMessage($messageDto);
        if($this->lastMessageRepository->getLastMessage($messageDto['conversation_id'])!=null){
            $this->lastMessageRepository->updateLastMessage($messageDto['conversation_id'],$messageDto['user_id']);
        }else{
            $this->lastMessageRepository->createLastMessage($messageDto['conversation_id'],$messageDto['user_id']);
        }
        broadcast(new MessageSent($messageDto,$this->conversationUserRepository->getReceiverId($messageDto['conversation_id'])))->toOthers();
    }
}
