<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
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



}
