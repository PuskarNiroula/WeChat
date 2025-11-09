<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function getSidebar(Request $request)
    {
        $userId = Auth::id();
        $perPage = 10;
        $page=$request->get('page', 1);

        // Step 1: Get conversation IDs
        $conversationIds = ConUser::where('user_id', $userId)
            ->pluck('conversation_id');

        // Step 2: Fetch all messages for those conversations
        $messages = Message::whereIn('conversation_id', $conversationIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('conversation_id')
            ->values();

        // Step 3: Manually paginate
        $total = $messages->count();
        $paginated = $messages->forPage($page, $perPage)->values();

        // Step 4: Prepare response with pagination info
        return response()->json([
            'data' => $paginated,
            'current_page' => (int) $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
        ]);
    }





}
