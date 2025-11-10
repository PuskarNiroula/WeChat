<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\LastMessage;
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
        $id=Auth::id();
        $con_id=ConUser::where("user_id",$id)->pluck("conversation_id");
        $message=LastMessage::whereIn('conversation_id',$con_id)
            ->orderBy('created_at','desc')
            ->with(['message',"user"])
            ->paginate(10);
        $transformed = $message->getCollection()->map(function ($item) {


            foreach ($item->message->conversation->conUsers as $conv) {
                if ($conv->user_id !== Auth::id()) {
                    $memberId=$conv->user->id;
                    $memberName=$conv->user->name;
                    break;
                }
            }
            return [
                'conversation_id' => $item->conversation_id,
                'last_message' => $item->message->message ?? null,
                "is_read" => $item->message->is_read,
                'last_message_time' => $item->message->created_at,
                'last_message_sender' => $item->message->user->id==Auth::id()?"Myself":$item->message->user->name,
                'chat_member' => $memberName,
                'chat_member_id' => $memberId,
            ];
        });
        $message->setCollection($transformed);
        return response()->json($message);
    }





}
