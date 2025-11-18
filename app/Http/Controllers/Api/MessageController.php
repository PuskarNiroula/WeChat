<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\LastMessage;
use App\Models\Message;
use App\Models\User;
use App\Service\ConversationUserService;
use App\Service\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class MessageController extends Controller
{
    protected ConversationUserService $conversationUserService;
    protected MessageService $messageService;
    public function __construct()
    {
        $this->conversationUserService = new ConversationUserService();
        $this->messageService = new MessageService();
    }

    public function getChunkMessages(int $conversation_id):JsonResponse
    {
        if ($conversation_id == 0 || $conversation_id == null) {
            return response()->json(['error' => 'hello world'], 403);
        }
        $userId = Auth::id();
        $conversationId = $conversation_id;


        $exists = ConUser::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        $transformed = $messages->getCollection()->map(function ($item) {

            return [
                'sender_id' => $item->sender_id,
                'message' => $item->message,
                'time'=>$item->created_at,
            ];
        });

        $messages->setCollection($transformed);
        return response()->json($messages);
    }
public function sendMessage(Request $request){
        $conversation_id=$request->conversation_id;
        $message=$request->message;

        $maxLength = 1000;
        if (empty($message) || !is_string($message) || trim($message) === '') {
            return response()->json(['error' => 'Message cannot be empty.'], 422);
        }
        if (mb_strlen($message) > $maxLength) {
            return response()->json(['error' => 'Message exceeds maximum length of ' . $maxLength . ' characters.'], 422);
        }

        if($conversation_id<1){
            return response()->json(['error' => 'Fuck you cheap hacker'], 403);
        }
        $messageDto=[
            'conversation_id'=>$conversation_id,
            'message'=>$message,
            'sender_id'=>Auth::id(),
        ];
           try{
            $this->messageService->createMessage($messageDto);
            return response()->json(['message' => 'Message sent successfully.']);
           }catch (\Exception $e){
            return response()->json([
                'status'=> "Failed to send message",
                'message'=> $e->getMessage(),
            ]);
           }
        }


    public function createOrFindConversation(int $user_id)
    {
        if ($user_id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid user id'
            ], 401);
        }
       try{
           $conversation=$this->conversationUserService->FindOrCreateConversation($user_id);
           return response()->json([
               'conversation_id' => $conversation->conversation_id,
               'name'=>$conversation->user->name,
               'id'=>$conversation->user->id,
               'avatar'=>$conversation->user->avatar??"avatar.jpg",
           ]);
       }catch (\Exception $e){
            return response()->json([
                'status'=> "Failed to create conversation",
                'message'=> $e->getMessage(),
            ]);
       }
    }

}
