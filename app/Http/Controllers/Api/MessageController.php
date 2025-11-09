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
    public function getChunkMessages(Request $request):JsonResponse
    {
        $userId = Auth::id();
        $conversationId = $request->conversation_id;


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
