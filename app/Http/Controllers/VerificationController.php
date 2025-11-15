<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify($id, $hash):JsonResponse{

        $user = User::findOrFail($id);


        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return response()->json(['message' => 'Email verified successfully']);

    }
    public function resend(Request $request):JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }

}
