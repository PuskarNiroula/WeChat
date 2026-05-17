<?php

namespace App\Http\Controllers\Api;

use App\Dto\PrivateConversationUserDto;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\User;
use App\Service\ConversationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $reponseModel=$this->conversationService->checkIfPrivateConversationExist($receiverId,auth()->id());
        if($reponseModel)
            return response()->json(
                $reponseModel
            );

        return response()->json(null);
    }

    public function createPrivateConversation(Request $request): JsonResponse
    {

        $request->validate([
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer|different:sender_id',
            'encrypted_room_key_for_sender' => 'required|string',
            'encrypted_room_key_for_receiver' => 'required|string',
        ]);



        if ((int)$request->input('sender_id') !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }


        $existingConversationId = $this->conversationService
            ->checkIfPrivateConversationExist(
                $request->input('receiver_id'),
                $request->input('sender_id')
            );
//
        if ($existingConversationId) {
            return response()->json([
                'status' => 'exists',
                'conversation_id' => $existingConversationId
            ],500);
        }
//
        $conversationUserDtoList = [];

        $senderDto = new PrivateConversationUserDto();
        $senderDto->userId = $request->sender_id;
        $senderDto->encryptedKey = $request->encrypted_room_key_for_sender;

        $receiverDto = new PrivateConversationUserDto();
        $receiverDto->userId = $request->input('receiver_id');
        $receiverDto->encryptedKey = $request->input('encrypted_room_key_for_receiver');

        $conversationUserDtoList[] = $senderDto;
        $conversationUserDtoList[] = $receiverDto;


       try{
           $conversation = $this->conversationService
               ->createPrivateConversation($conversationUserDtoList);

           return response()->json(
               $conversation);
       }catch (Exception $e){
           return response()->json([
               'status'=> "Failed to create conversation",
               'message'=> $e->getMessage(),
           ],500);
       }
    }
    public function getRoomKey(int $conversationId)
    {
        $user = ConUser::where('conversation_id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'room_key' => $user->encrypted_room_key
        ]);
    }

}
