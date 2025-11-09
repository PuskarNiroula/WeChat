<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ExtraController extends Controller{
    public function search(string $searchTerm):JsonResponse{
        if(trim($searchTerm)==""){
            return response()->json(['error' => 'Search term cannot be empty'], 400);
        }
        $users=User::where('name','like','%'.$searchTerm.'%')
            ->limit(10)
            ->get()
            ->pluck('name');
        return response()->json($users,200);
    }
    public function getSidebar()
    {
        $userId = Auth::id();

        // Get all conversation IDs the user is in
        $conversationIds = ConUser::where('user_id', $userId)->pluck('conversation_id');

        // Efficient query: get the latest message per conversation
        $latestMessages = Message::select('messages.*')
            ->whereIn('conversation_id', $conversationIds)
            ->whereRaw('messages.id IN (
            SELECT MAX(id)
            FROM messages
            WHERE conversation_id IN (' . $conversationIds->implode(',') . ')
            GROUP BY conversation_id
        )')
            ->orderBy('created_at', 'desc')
            ->with('user:id,name,email')
            ->paginate(10);

        return response()->json($latestMessages);
    }



}
