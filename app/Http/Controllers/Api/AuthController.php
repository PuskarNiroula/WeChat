<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register user
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=> 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),
        ]);

        // Create token
        $token = $user->createToken('chatapp')->plainTextToken;

        return response()->json([
            'user'=> $user,
            'token'=> $token,
        ], 201);
    }

    // Login user

    /**
     * @throws ValidationException
     */
    public function login(Request $request):JsonResponse
    {
        $request->validate([
            'email'=> 'required|email',
            'password'=> 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken("Chat Api");
        $token->accessToken->expires_at = now()->addHours(2);
        $token->accessToken->save();

        return response()->json([
            'user'=> $user,
            'token'=> $token->plainTextToken,
        ]);

    }

    // Logout user
    public function logout(Request $request):JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message'=> 'Logged out successfully'
        ]);
    }

    // Get current user
    public function me(Request $request):JsonResponse
    {
        return response()->json($request->user());
    }
}
