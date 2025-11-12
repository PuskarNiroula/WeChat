<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller{

    public function updateProfile(Request $request){
        $message="yes yes yes";
        $valid = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120', 'regex:/^[\p{L}\p{N}\p{Zs}\p{P}\p{S}]+$/u'],
            'avatar' => 'file|mimes:jpeg,png,jpg,gif,svg,webp|max:1024'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors(), 400);
        }

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $user = Auth::user();

            $file = $request->file('avatar');

            // Generate a unique filename with extension
            $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Save the file to public/images/avatars
            $file->move(public_path('images/avatars'), $filename);

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path('images/avatars/'.$user->avatar))) {
                unlink(public_path('images/avatars/'.$user->avatar));
            }
            $user->avatar = $filename;
            $message="yes";
        }
        else{
            $message="no file";

        }

        $user->name=$request->name;
        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'avatar' => $message]);
    }


}
