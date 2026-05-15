<?php

namespace App\Http\Controllers\Api;

use App\Dto\PrivateConversationUserDto;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Service\ConversationService;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    private ConversationService $conversationService;
    public function __construct()
    {
        $this->conversationService=new ConversationService();
    }

    public function checkConversation(int $receiverId):JsonResponse{
        if(!User::find($receiverId))
            return response()->json(['message'=>'Receiver not found']);

        $id=$this->conversationService->checkIfPrivateConversationExist($receiverId,auth()->id());
        if($id)
            return response()->json([
                'status'=>'success',
                'message'=>'Conversation already exist',
                'conversation_id'=>$id,
            ]);

        return response()->json(null);
    }

    public function createPrivateConversation(Request $request):JsonResponse{
        if (empty($request->keys))
            return response()->json(["message" => "Keys are required"],500);


        $keys=$request->keys;
        $conversationUserDtoList= [];
        foreach ($keys as $key){
            $dto = new PrivateConversationUserDto();
            $dto->userId = $key->userId;
            $dto->encryptedKey = $key->encryptedKey;
            $conversationUserDtoList[] = $dto;
        }
        $this->conversationService->createPrivateConversation($conversationUserDtoList);


        return response()->json([
            'status'=>'success',
            'message'=>'Conversation created successfully'
        ]);
    }

}
