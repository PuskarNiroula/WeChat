<?php

namespace App\Http\Controllers;


use App\Service\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;


class HomeController extends Controller{
    protected UserService $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

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
        if(!$request->user()->hasVerifiedEmail()){
            return response()->json([
                'status'=> "Error",
                'message'=> "Please verify your email address to login"
            ],403);
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

    public function logoutWeb(Request $request): RedirectResponse
    {
        $token=PersonalAccessToken::where('name',$request->session()->getId())->first();
        $token->delete();
        $request->session()->invalidate();
        Auth::logout();
        return redirect()->route('loginPage');
    }
    public function gotoRegisterPage():view{
        return view('Auth.Register');
    }
    public function registerWeb(Request $request): JsonResponse
    {
        $valid=validator($request->all(),[
            'name'=>['required','string','max:255'],
            'email'=>['required','string','email','max:255','unique:users'],
            'password'=>['required','string','min:4','same:confirmation']
        ]);
        if($valid->fails()){
            return response()->json($valid->errors(),400);
        }
        try {
            $user = $this->userService->createUser($request->all());
            return response()->json([
                'status' => "User created successfully",
                'user' => $user,
                'message' => 'Please verify your email address to login'
            ], 201);
        }catch (\Exception $e){
            return response()->json([
                'status'=> "Failed to register",
                'message'=> $e->getMessage()
            ]);
        }
    }
    public function dashboard():view{
        return view('Main.dashboard');
    }
    public function profile():view{
        return view('Main.profile');
    }
    public function forgotPassword():view{
        return view('Auth.Forget_Password');
    }
    public function resetPassword():view{
        return view('Auth.Reset_Password');
    }
}
