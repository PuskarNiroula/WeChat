<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dotenv\Validator;
use Illuminate\Http\Request;

class ProfileController extends Controller{

    public function updateProfile(Request $request){
        $valid=Validator::make($request->all(),[
           $request->name=>"string|max:120|required",
            $request->avatar=>"file|max:1024|mimes:jpeg,png,jpg,gif,svg,webp"
        ]);
        if($valid->fails()){
            return response()->json($valid->errors(),400);
        }
        return response()->json($request->all());

    }

}
