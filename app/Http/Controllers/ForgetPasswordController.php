<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgetPasswordController extends Controller
{
    public function resetLink(Request $request):JsonResponse{
        $email=$request->validate(['email' => 'required|email']);
        $user=User::where('email',$email)->first();
if(!$user)
    return response()->json(['message'=>'Email not found']);


        try {
            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert([
                'email' => $email,
                [
                    'token' => $token,
                    "created_at" => now()
                ]
            ]);
            $url = url('/reset-password/{$token}?email={$user->email}');
            Mail::raw("Reset your Password Here:" . $url, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Password');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Reset link sent'
            ]);
        }catch (\Exception $e){
            return response()->json([
                'status'=> "Failed to send reset link",
                'message'=> $e->getMessage(),
            ],500);
        }
    }
}
