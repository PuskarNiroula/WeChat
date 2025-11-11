<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\LastMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtraController extends Controller{
    public function search(string $searchTerm):JsonResponse{
        if(trim($searchTerm)==""){
            return response()->json(['error' => 'Search term cannot be empty'], 400);
        }
        $users=User::where('name','like','%'.$searchTerm.'%')
            ->limit(10)
            ->get()
            ->pluck('name','id');


        return response()->json($users,200);
    }
    public function getSidebar(Request $request)
    {
        $userId = Auth::id();

        $conversationIds = ConUser::where('user_id', $userId)->pluck('conversation_id');

        $messages = LastMessage::whereIn('conversation_id', $conversationIds)
            ->with(['message.user', 'message.conversation.conUsers'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->filter(fn($item) => $item->message !== null)
            ->unique('conversation_id');

        // Transform for frontend
        $transformed = $messages->map(function ($item) use ($userId) {
            $memberName = $memberId = null;

            foreach ($item->message->conversation->conUsers as $conv) {
                if ($conv->user_id != $userId) {
                    $memberId = $conv->user->id;
                    $memberName = $conv->user->name;
                    break;
                }
            }

            return [
                'conversation_id' => $item->conversation_id,
                'last_message' => $item->message->message ?? null,
                'is_read' => $item->message->is_read ?? null,
                'last_message_time' => $item->message->created_at ?? null,
                'last_message_sender' => $item->message->user->id == $userId ? 'Myself' : $item->message->user->name,
                'chat_member' => $memberName,
                'chat_member_id' => $memberId,
            ];
        });

        return response()->json($transformed->values());
    }







}
