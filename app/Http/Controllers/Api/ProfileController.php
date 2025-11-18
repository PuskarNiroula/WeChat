<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService){
        $this->userService=$userService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $valid = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120', 'regex:/^[\p{L}\p{N}\p{Zs}\p{P}\p{S}]+$/u'],
            'avatar' => 'file|mimes:jpeg,png,jpg,gif,svg,webp|max:1024'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors(), 400);
        }

        try {
            $data = [
                'name' => $request->name,
                'avatar' => $request->file('avatar') ?? null
            ];

            $this->userService->updateProfile($data);

            return response()->json(['message' => 'Profile updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


}
