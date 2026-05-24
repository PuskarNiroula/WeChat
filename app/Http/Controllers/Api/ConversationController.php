<?php

namespace App\Http\Controllers\Api;

use App\Dto\PrivateConversationUserDto;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\User;
use App\Service\ConversationChecker;
use App\Service\ConversationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    private ConversationService $conversationService;
    private ConversationChecker $conversationChecker;
    public function __construct()
    {
        $this->conversationService=new ConversationService();
        $this->conversationChecker=new ConversationChecker();
    }

    public function checkConversation($receiverId):JsonResponse{
        if($receiverId==null)
            return response()->json(null);


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
    public function getRoomKey(int $conversationId,Request $request)
    {
        $request->validate([
            'version' => 'required|integer'
        ]);
        $user = ConUser::where('conversation_id', $conversationId)
            ->where('user_id', auth()->id())
            ->where('key_version',$request->query('version'))
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
    public function getConversationMeta(int $conversationId): JsonResponse
    {
        if(!$this->conversationChecker->IsUserInConversation($conversationId,auth()->id())){
            return response()->json([
                'message'=>"You are not in this conversation"
            ],401);
        }

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->type === 'group') {
            $role = $conversation->conUsers()->where('user_id', auth()->id())
            ->where('is_admin', true)->exists();
            return response()->json([
                'id' => $conversation->id,
                'type' => $conversation->type,
                'name' => $conversation->name,
                'avatar' => $conversation->image,
                'is_group' => true,
                'is_admin' => $role
            ]);
        }

        $conUser = ConUser::where('conversation_id', $conversationId)
            ->where('user_id', '!=', auth()->id())
            ->first();

        return response()->json([
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conUser?->user?->name ?? 'Unknown',
            'avatar' => $conUser?->user?->avatar ?? 'avatar.jpg',
            'is_group' => false,
        ]);
    }

    public function updateConversation(int $conversationId,Request $request): JsonResponse
    {
        if(!$this->conversationChecker->IsUserInConversation($conversationId,auth()->id())){
            return response()->json([
                'message'=>"You are not in this conversation"
            ],401);
        }

        $request->validate([
            'name' => 'required|string',
            'image' => 'nullable|image'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $destination = public_path('images/avatars');

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $file->move($destination, $filename);

            $imagePath = $filename;
        }

        $data = [
            'name' => $request->name,
            'image' => $imagePath
        ];

       try{
           $result = $this->conversationService->updateConversation($conversationId, $data);
           return response()->json([
               'message' => 'Conversation updated successfully',
               'data' => $result
           ]);
       }catch (Exception $e){
           if($request->hasFile('image')) {
               unlink(public_path('images/avatars/' . $imagePath));
           }

           return response()->json([
               'status'=> "Failed to update conversation",
               'message'=> $e->getMessage(),
           ]);
       }
    }

    public function getLatestKey(int $conversationId){
        if(!$this->conversationChecker->IsUserInConversation($conversationId,auth()->id())){
            return response()->json([
                'message'=>"You are not in this conversation"
            ],401);
        }

        $version = Conversation::find($conversationId);
        return response()->json($version['latest_key_version']);
    }
    public function getSharedKeyAccordingToVersion(int $conversationId,Request $request){
        if(!$this->conversationChecker->IsUserInConversation($conversationId,auth()->id())){
            return response()->json([
                'message'=>"You are not in this conversation"
            ],401);
        }
        $keyVersion = $request->query('version');
        $conversationUser = ConUser::where('conversation_id',$conversationId)
            ->where('user_id',auth()->id())
            ->where('key_version',$keyVersion)
            ->first();
        return response()->json($conversationUser['encrypted_room_key']);
    }

}
