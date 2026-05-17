<?php

namespace App\Http\Controllers\Api;

use App\Dto\ChatMember;
use App\Dto\GroupChatCreateDto;
use App\Http\Controllers\Controller;
use App\Service\ConversationService;
use Exception;
use Illuminate\Http\Request;

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
            return response()->json($this->conversationService->createGroupChat($groupChat, auth()->id()));
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }

    }

}
