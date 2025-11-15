<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        if($user->email_verified_at==null){
            return response()->json([
                'status'=> "Error",
                'message'=> "Please verify your email address to login"
            ],403);
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


    public function sendResetLink(Request $request):JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $token = Str::random(64);

        // Save token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        // Send reset email
        $url = url("/resetPassword/{$token}?email={$request->email}");
        Mail::raw("Click here to reset your password: {$url}", function($message) use ($request) {
            $message->to($request->email)
                ->subject('Password Reset Request');
        });

        return response()->json(['message' => 'Password reset email sent']);

    }
    public function resetPassword(Request $request,$token,$email):JsonResponse
    {
        $valid=validator($request->all(),[
            'password' => 'required|string|min:6|same:password_confirmation|confirmed',
        ]);
        if($valid->fails()){
            return response()->json($valid->errors(),400);
        }

        if(
            DB::table('password_reset_tokens')->where('email',$email)
            ->where('token',$token)->exists()
        ){
            $user=User::where('email',$email)->first();
            $user->password=$request->password;
            $user->save();
            return response()->json(['message'=>'Password reset successfully']);
        }
        return response()->json(['message'=>'Invalid token'],400);
    }

    // Get current user
    public function me(Request $request):JsonResponse
    {
        return response()->json($request->user());
    }
}
