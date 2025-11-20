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
    protected ConversationUserService $conversationUserService;
    public function __construct()
    {
        $this->messageRepository=app(MessageRepositoryInterface::class);
        $this->lastMessageRepository=app(LastMessageRepositoryInterface::class);
        $this->conversationUserRepository=app(ConversationUserRepositoryInterface::class);
        $this->conversationUserService=app(ConversationUserService::class);
    }


    /**
     * @throws \Exception
     */
    public function createMessage(array $messageDto){
        if(!$this->conversationUserService->checkValidConversation($messageDto['conversation_id'],auth()->id()))
            throw new \Exception("Not your conversation");
        DB::beginTransaction();
       $message= $this->messageRepository->createMessage($messageDto);
        if($this->lastMessageRepository->checkIfLastMessageExist($messageDto['conversation_id'])){
            $this->lastMessageRepository->updateLastMessage($messageDto['conversation_id'],$message->id);
        }else{
            $this->lastMessageRepository->createLastMessage($messageDto['conversation_id'],$message->id);
        }
        DB::commit();
        broadcast(new MessageSent( $this->conversationUserRepository->getReceiverId($messageDto['conversation_id']),$messageDto['conversation_id']))->toOthers();
        //caching the data to redis
        app(ChatCacheService::class)->pushMessage($messageDto['conversation_id'],$message['message'],$messageDto['sender_id'],now());
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

    public function getPaginatedMessages(int $conversation_id):array{
        if(!$this->conversationUserService->checkValidConversation($conversation_id,auth()->id())){
            throw new \Exception("Conversation doesn't belong to you");
        }

        $cache=app(ChatCacheService::class)->getMessage($conversation_id);
        if(!empty($cache)){
            return [
                'source'=>'redis',
                'messages'=>$cache
            ];
        }
        $messages= $this->messageRepository->getMessagesByConversation($conversation_id);
        $this->messageRepository->markAsRead($conversation_id);
        $transformed = $messages->getCollection()->map(function ($item) {

            return [
                'source'=>"database",
                'sender_id' => $item->sender_id,
                'message' => $item->message,
                'time'=>$item->created_at,
            ];
        });
        //pulling data to redis
        foreach($transformed->values()->toArray() as $message){
            app(ChatCacheService::class)->pushMessage(
                $conversation_id,
                $message['message'],
                $message['sender_id'],
                $message['time']
            );
        }
        return $transformed->values()->toArray();
    }

}
