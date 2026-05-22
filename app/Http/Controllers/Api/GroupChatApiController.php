<?php

namespace App\Http\Controllers\Api;

use App\ApiResponseModel\GroupMemberApiResponseModel;
use App\Dto\ChatMember;
use App\Dto\GroupChatCreateDto;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\User;
use App\Service\ConversationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function PHPSTORM_META\map;

class GroupChatApiController extends Controller
{
    private ConversationService $conversationService;

    public function __construct()
    {
        $this->conversationService = new ConversationService();
    }

    public function createGroupChat(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'userData' => 'required|array'
        ]);

        $groupChat = new GroupChatCreateDto();
        $groupChat->name = $request->name;

        $chatMembers = $request->userData;
        foreach ($chatMembers as $userId => $encryptedKey) {
            $chatMember = new ChatMember();
            $chatMember->setUserId($userId);
            $chatMember->setEncryptedKey($encryptedKey);
            if ($userId == auth()->id()) {
                $chatMember->setAdmin();
            }
            $groupChat->addMember($chatMember);
        }
        try {
            return response()->json($this->conversationService->createGroupChat($groupChat));
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }

    }

    public function getGroupMembers(int $groupId): JsonResponse
    {
        $members = ConUser::where('conversation_id', $groupId)
            ->with('user')
            ->select('user_id')
            ->distinct()
            ->get();

        $response = [];

        foreach ($members as $member) {
            $vm = new GroupMemberApiResponseModel();
            $vm->userId = $member->user->id;
            $vm->name = $member->user->name;
            $response[] = $vm;
        }

        return response()->json($response);
    }

    public function searchNewMember(int $groupId, Request $request):JsonResponse{
        $request->validate([
            'user' => 'required|string'
        ]);
        $oldUsers= $this->getGroupChatMemberIds($groupId);
        $ids = $oldUsers->pluck('user_id');

        $users = User::whereNotIn('id', $ids)
            ->where('name', 'LIKE', '%' . $request->user . '%')
            ->get();

        $response=[];
        foreach ($users as $user){
            $vm = new GroupMemberApiResponseModel();
            $vm->userId = $user->id;
            $vm->name = $user->name;
            if ($user->avatar) {
                $vm->avatar = "/images/avatars/".$user->avatar;
            } else {
                $vm->avatar = "/images/avatars/avatar.jpg";
            }
            $response[]=$vm;
        }

        return response()->json($response);
    }

    private function getGroupChatMemberIds(int $groupId){
        return ConUser::where('conversation_id', $groupId)
            ->with('user')
            ->get()
            ->unique('user_id');
    }
    public function addNewMembers(Request $request){
        $request->validate([
            'userData' => 'required|array',
            'conversationId' => 'required|integer'
        ]);


        $chatMembers = $request->userData;
        $listOfMembers=[];
        foreach ($chatMembers as $userId => $encryptedKey) {
            $chatMember = new ChatMember();
            $chatMember->setUserId($userId);
            $chatMember->setEncryptedKey($encryptedKey);
            $listOfMembers[]=$chatMember;
        }
        try {
           $this->conversationService->addGroupMembers($listOfMembers,$request->conversationId);
           return response()->json([
               'status' => 'success',
               'message' => 'Members added successfully'
           ],200);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }


    }
    public function removeMembers(Request $request){
        $request->validate([
            'userData' => 'required|array',
            'conversationId' => 'required|integer',
            'removedUserIds'=>'required|array'
        ]);


        $chatMembers = $request->userData;
        $membersToRemove=$request->removedUserIds;
        $listOfMembers=[];
        foreach ($chatMembers as $userId => $encryptedKey) {
            $chatMember = new ChatMember();
            $chatMember->setUserId($userId);
            $chatMember->setEncryptedKey($encryptedKey);
            $listOfMembers[]=$chatMember;
        }
        try {
           $this->conversationService->addGroupMembers($listOfMembers,$request->conversationId);
           $this->conversationService->removeGroupMembers($membersToRemove,$request->conversationId);
           return response()->json([
               'status' => 'success',
               'message' => 'Members added successfully'
           ],200);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }


    }

}
