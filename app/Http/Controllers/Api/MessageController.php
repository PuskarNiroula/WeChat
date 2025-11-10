<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
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
            ->paginate(20);
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
        if(ConUser::where('conversation_id',$conversation_id)->where('user_id',$userId)->exists()){
            Message::create([
                'sender_id'=>$userId,
                'message'=>$message,
                'conversation_id'=>$conversation_id,
            ]);

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

        $myId = Auth::id();

        if ($myId == $user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot create a conversation with yourself'
            ]);
        }

        $commonConversation =ConUser::
            select('conversation_id')
            ->where('user_id', $myId)
            ->whereIn('conversation_id', function ($query) use ($user_id) {
                $query->select('conversation_id')
                    ->from('conversation_user')
                    ->where('user_id', $user_id);
            })
            ->first();
        $conversationId = $commonConversation->conversation_id??null;
        if(!$commonConversation) {
            $conv = Conversation::create([
                'type' => "private"
            ]);

            ConUser::create([
                'conversation_id' => $conv->id,
                "user_id" =>Auth::id()
            ]);
            ConUser::create([
                'conversation_id' => $conv->id,
                "user_id" =>$user_id
            ]);
            $conversationId=$conv->id;
        }
            return response()->json([
                'conversation_id' => $conversationId
            ]);
    }

}
