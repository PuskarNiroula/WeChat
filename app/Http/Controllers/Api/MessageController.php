<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\LastMessage;
use App\Models\Message;
use App\Models\User;
use App\Service\ConversationUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class MessageController extends Controller
{
    protected ConversationUserService $conversationUserService;
    public function __construct(ConversationUserService $conversationUserService)
    {
        $this->conversationUserService = $conversationUserService;
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
        $userId=Auth::id();
        $conversation_id=$request->conversation_id;
        $message=$request->message;

        // Validate message content
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
        $receiver_id=ConUser::where('conversation_id',$conversation_id)->where('user_id','!=',$userId)->pluck('user_id')->first();
        if(ConUser::where('conversation_id',$conversation_id)->where('user_id',$userId)->exists()){
           $message= Message::create([
                'sender_id'=>$userId,
                'message'=>$message,
                'conversation_id'=>$conversation_id,
            ]);
           $latestMessage=LastMessage::where('conversation_id',$conversation_id)->first();
           if(!$latestMessage){
               LastMessage::create([
                   'conversation_id'=>$conversation_id,
                   'message_id'=>$message->id,
               ]);
           }else{
               $latestMessage->message_id=$message->id;
               $latestMessage->save();
           }
           broadcast(new MessageSent($receiver_id,$conversation_id))->toOthers();
            return response()->json(['message'=>'Message sent successfully'],200);
        }
        return response()->json(['error' => 'Fuck you a bit educated hacker, try harder next time i have left 2 loop holes'], 403);
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
