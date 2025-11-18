<?php
namespace App\Service;

use App\Events\MessageSent;
use App\Interface\ConversationUserRepositoryInterface;
use App\Interface\LastMessageRepositoryInterface;
use App\Interface\MessageRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MessageService {
    protected MessageRepositoryInterface $messageRepository;
    protected LastMessageRepositoryInterface $lastMessageRepository;
    protected ConversationUserRepositoryInterface $conversationUserRepository;
    public function __construct()
    {
        $this->messageRepository=app(MessageRepositoryInterface::class);
        $this->lastMessageRepository=app(LastMessageRepositoryInterface::class);
        $this->conversationUserRepository=app(ConversationUserRepositoryInterface::class);
    }


    public function createMessage(array $messageDto){
        DB::beginTransaction();
       $message= $this->messageRepository->createMessage($messageDto);
        if($this->lastMessageRepository->checkIfLastMessageExist($messageDto['conversation_id'])){
            $this->lastMessageRepository->updateLastMessage($messageDto['conversation_id'],$message->id);
        }else{
            $this->lastMessageRepository->createLastMessage($messageDto['conversation_id'],$message->id);
        }
        DB::commit();
        broadcast(new MessageSent( $this->conversationUserRepository->getReceiverId($messageDto['conversation_id']),$messageDto['conversation_id']))->toOthers();
    }
    public function getSidebar(){
        $userId=auth()->id();
        $conversationIds=$this->conversationUserRepository->getConversationIdofUser(auth()->id());
        $messages= $this->lastMessageRepository->getSidebar($conversationIds);

        $transformed = $messages->map(function ($item) use ($userId) {
            $memberName = $memberId = null;

            foreach ($item->message->conversation->conUsers as $conv) {
                if ($conv->user_id != $userId) {
                    $memberId = $conv->user->id;
                    $memberName = $conv->user->name;
                    $avatar=$conv->user->avatar;
                    break;
                }
            }

            return [
                'conversation_id' => $item->conversation_id,
                'last_message' => $item->message->message ?? null,
                'is_read' => $item->message->is_read,
                'last_message_time' => $item->message->created_at ?? null,
                'last_message_sender' => $item->message->user->id == $userId ? 'Myself' : $item->message->user->name,
                'chat_member' => $memberName,
                'chat_member_id' => $memberId,
                "avatar"=>$avatar??"avatar.jpg",
            ];
        });
        return $transformed->values();
    }
}
