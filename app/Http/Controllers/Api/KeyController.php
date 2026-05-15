<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class KeyController extends Controller
{


    public function getPublicKey(int $receiverId): JsonResponse
    {
        $user = User::find($receiverId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->public_key) {
            return response()->json([
                'message' => 'User has no public key'
            ], 404);
        }

        return response()->json([
            'public_key' => $user->public_key
        ]);
    }

}
