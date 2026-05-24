<?php

namespace App\Http\Controllers\Api;

use App\ApiResponseModel\GroupMemberApiResponseModel;
use App\Dto\ChatMember;
use App\Dto\GroupChatCreateDto;
use App\Enums\ConversationUserStatus;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\User;
use App\Service\ConversationChecker;
use App\Service\ConversationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupChatApiController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly ConversationChecker $conversationChecker,
    ) {}

    public function createGroupChat(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string',
            'userData' => 'required|array',
        ]);

        $groupChat          = new GroupChatCreateDto();
        $groupChat->name    = $request->name;
        $groupChat->addMembers($this->buildChatMembers($request->userData));

        try {
            return response()->json([
                'status' => 'success',
                $this->conversationService->createGroupChat($groupChat,auth()->id())
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getGroupMembers(int $groupId): JsonResponse
    {
        if (!$this->isCurrentUserInConversation($groupId)) {
            return $this->unauthorizedResponse();
        }

        $members = ConUser::where('conversation_id', $groupId)
            ->with('user')
            ->where('status', ConversationUserStatus::ACTIVE)
            ->select('user_id')
            ->distinct()
            ->get();

        return response()->json(
            $members->map(fn ($member) => $this->toGroupMemberResponse($member->user))
        );
    }

    public function searchNewMember(int $groupId, Request $request): JsonResponse
    {
        if (!$this->isCurrentUserInConversation($groupId)) {
            return $this->unauthorizedResponse();
        }

        $request->validate(['user' => 'required|string']);

        $existingMemberIds = $this->getActiveMemberIds($groupId);

        $users = User::whereNotIn('id', $existingMemberIds)
            ->where('name', 'LIKE', "%{$request->user}%")
            ->get();

        return response()->json(
            $users->map(fn ($user) => $this->toGroupMemberResponse($user, withAvatar: true))
        );
    }

    public function addNewMembers(Request $request): JsonResponse
    {
        $request->validate([
            'userData'       => 'required|array',
            'conversationId' => 'required|integer',
        ]);

        if (!$this->isCurrentUserInConversation($request->conversationId)) {
            return $this->unauthorizedResponse();
        }

        try {
            $this->conversationService->addGroupMembers(
                $this->buildChatMembers($request->userData),
                $request->conversationId,
            );

            return $this->successResponse('Members added successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function removeMembers(Request $request): JsonResponse
    {
        $request->validate([
            'userData'       => 'required|array',
            'conversationId' => 'required|integer',
            'removedUserIds' => 'required|array',
        ]);

        if (!$this->conversationChecker->IsUserAdminInConversation($request->conversationId, auth()->id())) {
            return $this->unauthorizedResponse();
        }

        try {
            $this->conversationService->addGroupMembers(
                $this->buildChatMembers($request->userData),
                $request->conversationId,
            );

            $this->conversationService->removeGroupMembers(
                $request->removedUserIds,
                $request->conversationId,
            );

            return $this->successResponse('Members removed successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function leaveGroupChat(Request $request): JsonResponse
    {
        $request->validate([
            'userData'       => 'required|array',
            'conversationId' => 'required|integer',
        ]);

        if (!$this->isCurrentUserInConversation($request->conversationId)) {
            return $this->unauthorizedResponse();
        }

        try {
            $this->conversationService->addGroupMembers(
                $this->buildChatMembers($request->userData, skipSelf: true),
                $request->conversationId,
            );

            $this->conversationService->leaveGroup($request->conversationId, auth()->id());

            return $this->successResponse('You have left the group.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function isCurrentUserInConversation(int $conversationId): bool
    {
        return $this->conversationChecker->IsUserInConversation($conversationId, auth()->id());
    }

    /** @return int[] */
    private function getActiveMemberIds(int $groupId): array
    {
        return ConUser::where('conversation_id', $groupId)
            ->where('status', ConversationUserStatus::ACTIVE)
            ->get()
            ->unique('user_id')
            ->pluck('user_id')
            ->all();
    }

    /**
     * Build ChatMember DTOs from a userId => encryptedKey map.
     *
     * @param  array<int|string, string>  $userData
     * @return ChatMember[]
     */
    private function buildChatMembers(array $userData, bool $skipSelf = false): array
    {
        $members = [];

        foreach ($userData as $userId => $encryptedKey) {
            if ($skipSelf && (int) $userId === auth()->id()) {
                continue;
            }

            $member = new ChatMember();
            $member->setUserId($userId);
            $member->setEncryptedKey($encryptedKey);
            $members[] = $member;
        }

        return $members;
    }

    private function toGroupMemberResponse(User $user, bool $withAvatar = false): GroupMemberApiResponseModel
    {
        $vm         = new GroupMemberApiResponseModel();
        $vm->userId = $user->id;
        $vm->name   = $user->name;

        if ($withAvatar) {
            $vm->avatar = $user->avatar
                ? "/images/avatars/{$user->avatar}"
                : '/images/avatars/avatar.jpg';
        }

        return $vm;
    }

    private function successResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message], $status);
    }

    private function errorResponse(string $message, int $status = 403): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json(['message' => 'You are not authorized in this conversation.'], 401);
    }
}
