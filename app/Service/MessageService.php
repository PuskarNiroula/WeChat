<?php
namespace App\Service;

use App\Events\MessageSent;
use App\Interface\LastMessageRepositoryInterface;
use App\Interface\MessageRepositoryInterface;
use App\Models\Conversation;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

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
    public function createMessage(array $messageDto): void
    {
        $userId = auth()->id();
        $conversationId = $messageDto['conversation_id'];

        $this->ensureConversationAccess($userId, $conversationId);

        DB::beginTransaction();

        try {
            $message = $this->storeMessage($messageDto);
            $this->updateLastMessage($conversationId, $message->id);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->broadcastMessage($messageDto, $message);
        $this->cacheMessage($messageDto, $message);
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
                        $avatar = $conv->user->avatar??"avatar.jpg";
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
                'key_version' => $item->message->key_version,
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
                'key_version'=>$item->key_version,
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
                $message['key_version'],
                $message['avatar']
            );
        }
        return $transformed->values()->toArray();
    }

    /**
     * @throws Exception
     */
    private function ensureConversationAccess(int $userId, int $conversationId): void
    {
        if (!$this->conversationUserService->checkValidConversation($userId, $conversationId)) {
            throw new Exception("Not your conversation");
        }
    }

    private function storeMessage(array $messageDto)
    {

        return $this->messageRepository->createMessage($messageDto);
    }
    private function updateLastMessage(int $conversationId, int $messageId): void
    {
        if ($this->lastMessageRepository->checkIfLastMessageExist($conversationId)) {
            $this->lastMessageRepository->updateLastMessage($conversationId, $messageId);
            return;
        }

        $this->lastMessageRepository->createLastMessage($conversationId, $messageId);
    }
    private function broadcastMessage(array $messageDto): void
    {
        $conversation = Conversation::with('conUsers')->find($messageDto['conversation_id']);
        $senderId = $messageDto['sender_id'];

        if ($conversation->type === 'group') {
            $receiverIds = $conversation->conUsers->pluck('user_id');

            foreach ($receiverIds as $receiverId) {
                if ($receiverId == $senderId) continue;

                broadcast(new MessageSent(
                    $receiverId,
                    $conversation->id,
                    $messageDto['message'],
                    now()
                ))->toOthers();
            }

            return;
        }

        $receiverId = $this->conversationUserService
            ->getReceiverId($conversation->id);

        broadcast(new MessageSent(
            $receiverId,
            $conversation->id,
            $messageDto['message'],
            now()
        ))->toOthers();
    }
    private function cacheMessage(array $messageDto, $message): void
    {
        $avatar = User::find($messageDto['sender_id'])->avatar ?? "avatar.jpg";

        app(ChatCacheService::class)->pushMessage(
            $messageDto['conversation_id'],
            $message['encrypted_message'],
            $messageDto['iv'],
            $messageDto['sender_id'],
            now(),
            $messageDto['key_version'],
            $avatar
        );
    }
}
