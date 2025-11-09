<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getChunkMessages(int $conversation_id):JsonResponse
    {
        if($conversation_id == 0|| $conversation_id == null){
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
            ->orderBy('created_at', 'asc') // oldest first for chat display
            ->paginate(20);

        return response()->json($messages);
    }



}
