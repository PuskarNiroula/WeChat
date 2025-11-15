<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function gotoChangePassword(){
        return view('Auth.ChangePassword');
    }
    public function gotoChangeEmail(){
        return view('Auth.ChangeEmail');
    }
    public function updatePassword(Request $request){
        return "hello world";

    }
    public function updateEmail(Request $request){
        return "hello world";

    }

}
