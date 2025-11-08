<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller{

    public function gotoLoginPage(){
        return view('Auth.login');
    }
    public function loginWeb(Request $request){
        Auth::attempt($request->only('email', 'password'));
        $request->session();
        Auth::login($request->user());
        $user=Auth::user();
        $token = $user->createToken('chatapp')->plainTextToken;

        return response()->json([
            'user'=> $user,
            'token'=> $token,
        ]);



    }
}
