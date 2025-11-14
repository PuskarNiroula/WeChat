<?php

namespace App\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class HomeController extends Controller{

    public function gotoLoginPage():view{
        return view('Auth.login');
    }

    public function loginWeb(Request $request):JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }
      try{
          $request->session();
          Auth::login($request->user());
          $user = Auth::user();

          $token = $user->createToken($request->session()->getId());
          $token->accessToken->expires_at = now()->addHours(2);
          $token->accessToken->save();

          return response()->json([
              'user'=> $user,
              'token'=> $token->plainTextToken,
          ]);
      }catch (\Exception $e){
          return response()->json([
              'status'=> "Failed to login",
              'message'=> $e->getMessage()
          ],500);

      }
    }

    public function logoutWeb(Request $request){
        $token=PersonalAccessToken::where('name',$request->session()->getId())->first();
        $token->delete();
        $request->session()->invalidate();
        Auth::logout();
        return redirect()->route('loginPage');
    }
    public function dashboard():view{
        return view('Main.dashboard');
    }
    public function profile():view{
        return view('Main.profile');
    }
}
