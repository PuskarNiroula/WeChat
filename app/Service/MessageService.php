<?php
namespace App\Service;

use App\Events\MessageSent;
use App\Interface\LastMessageRepositoryInterface;
use App\Interface\MessageRepositoryInterface;
use App\Models\ConUser;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class MessageService {
    protected MessageRepositoryInterface $messageRepository;
    protected LastMessageRepositoryInterface $lastMessageRepository;
    protected ConversationUserService $conversationUserService;
    public function __construct()
    {
        $this->messageRepository=app(MessageRepositoryInterface::class);
        $this->lastMessageRepository=app(LastMessageRepositoryInterface::class);
        $this->conversationUserService=app(ConversationUserService::class);
    }


    /**
     * @throws Exception
     */
    public function createMessage(array $messageDto):void{
        if(!$this->conversationUserService->checkValidConversation(auth()->id(),$messageDto['conversation_id']))
            throw new Exception("Not your conversation");
        DB::beginTransaction();
       $message= $this->messageRepository->createMessage($messageDto);
        if($this->lastMessageRepository->checkIfLastMessageExist($messageDto['conversation_id'])){
            $this->lastMessageRepository->updateLastMessage($messageDto['conversation_id'],$message->id);
        }else{
            $this->lastMessageRepository->createLastMessage($messageDto['conversation_id'],$message->id);
        }
        DB::commit();
        $avatar=User::find($messageDto['sender_id'])->avatar??"avatar.jpg";
        broadcast(
            new MessageSent(
                $this->conversationUserService->getReceiverId($messageDto['conversation_id']),
                $messageDto['conversation_id'],
                $messageDto['message'],
                now()
            )
        )->toOthers();



        app(ChatCacheService::class)->pushMessage($messageDto['conversation_id'],$message['encrypted_message'],$messageDto['iv'],$messageDto['sender_id'],now(),$avatar);
    }
    public function getSidebar()
    {
        $userId = auth()->id();

        $conversationIds = $this->conversationUserService->getConversationIdOfUser($userId);
        $messages = $this->lastMessageRepository->getSidebar($conversationIds);

        $transformed = $messages->map(function ($item) use ($userId) {

            $conversation = $item->message->conversation;

            $isGroup = $conversation->type === 'group';

            $chatName = null;
            $avatar = null;
            $memberId = null;

            if ($isGroup) {

                $chatName = $conversation->name ?? 'Group Chat';


                $avatar = $conversation->image ?? 'default_group_image.png';

            } else {

                foreach ($conversation->conUsers as $conv) {
                    if ($conv->user_id != $userId) {
                        $memberId = $conv->user->id;
                        $chatName = $conv->user->name;
                        $avatar = $conv->user->avatar;
                        break;
                    }
                }
            }

            return [
                'conversation_id' => $item->conversation_id,
                'is_group' => $isGroup,

                'chat_name' => $chatName,
                'chat_member_id' => $memberId,

                'last_message' => $item->message->encrypted_message ?? null,
                'is_read' => $item->message->is_read,
                'last_message_time' => $item->message->updated_at ?? null,
                'last_message_sender' =>
                    $item->message->user->id == $userId ? 'Myself' : $item->message->user->name,

                'iv' => $item->message->iv,
                'avatar' => $avatar ?? "avatar.jpg",
            ];
        });

        return $transformed->values();
    }

    /**
     * @throws Exception
     */
    public function getPaginatedMessages(int $conversation_id):array{
        if(!$this->conversationUserService->checkValidConversation(auth()->id(),$conversation_id)){
            throw new Exception("Conversation doesn't belong to you");
        }
        $this->messageRepository->markAsRead($conversation_id);

        $cache=app(ChatCacheService::class)->getMessage($conversation_id);
        if(!empty($cache)){
            return [
                'source'=>'redis',
                'messages'=>$cache
            ];
        }
        $messages= $this->messageRepository->getMessagesByConversation($conversation_id);

        $transformed = $messages->getCollection()->map(function ($item) {

            return [
                'source'=>"database",
                'sender_id' => $item->sender_id,
                'message' => $item->encrypted_message,
                'avatar'=>User::find($item->sender_id)->avatar??"avatar.jpg",
                'iv'=>$item->iv,
                'time'=>$item->created_at,
            ];
        });
        foreach($transformed->values()->toArray() as $message){

            app(ChatCacheService::class)->pushMessage(
                $conversation_id,
                $message['message'],
                $message['iv'],
                $message['sender_id'],
                $message['time'],
                $message['avatar']
            );
        }
        return $transformed->values()->toArray();
    }

}
