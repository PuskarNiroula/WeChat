<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Service\UserService;
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
    private UserService $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    // Register user
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=> 'required|email|unique:users,email',
            'password' => 'required|string|min:6|same:confirmation',
        ]);

        try{
            $user=$this->userService->createUser($request->all());
            $token = $user->createToken('chatapp')->plainTextToken;

            return response()->json([
                'user'=> $user,
                'token'=> $token,
            ], 201);
        }catch (\Exception $e){
            return response()->json([
                'status'=> "Failed to register",
                'message'=> $e->getMessage()
            ]);
        }



        // Create token

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
        if(!$user->hasVerifiedEmail()){
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

        $user=User::where('email',$request->email)->first();
        if(!$user){
            //wrong message to make attacker fool
            return response()->json(['message'=>'Email send Successfully'],404);
        }

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
            'password' => 'required|string|min:6|same:password_confirmation',
        ]);
        if($valid->fails()){
            return response()->json($valid->errors(),400);
        }
        $myResetToken=DB::table('password_reset_tokens')->where('email',$email)->where('token',$token)->first();

        if(
            DB::table('password_reset_tokens')->where('email',$email)
            ->where('token',$token)->exists()
        ){
            $createdAt =$myResetToken->created_at;

            if (!$createdAt) {
                return response()->json(['message' => 'Invalid token'], 400);
            }

            //check if more than an hour has passed if so invalidate that token
            if (Carbon::parse($createdAt)->addHour()->isPast()) {
                return response()->json(['message' => 'Token expired'], 400);
            }
            $user=User::where('email',$email)->first();
            $user->password=$request->password;
            $user->save();
            $myResetToken->delete();
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
