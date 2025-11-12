<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $valid = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120', 'regex:/^[\p{L}\p{N}\p{Zs}\p{P}\p{S}]+$/u'],
            'avatar' => 'file|mimes:jpeg,png,jpg,gif,svg,webp|max:1024'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors(), 400);
        }

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            // Generate a unique filename with extension
            $extension = $file->getClientOriginalExtension();
            // Validate extension is in allowed list
            if (!in_array(strtolower($extension), ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'])) {
                return response()->json(['message' => 'Invalid file type'], 400);
            }
            $filename = uniqid() . '_' . time() . '.' . $extension;


                // Save the file to public/images/avatars
                try{
                    $file->move(public_path('images/avatars'), $filename);
                }catch (\Exception){
                    return response()->json(['message' => 'Failed to upload avatar'], 500);
                }

                // Delete old avatar if exists
                if ($user->avatar && file_exists(public_path('images/avatars/' . $user->avatar))) {
                   try{
                       unlink(public_path('images/avatars/' . $user->avatar));
                   }catch (\Exception){
                       return response()->json(['message' => 'Failed to delete old avatar'], 500);
                   }
                }
                $user->avatar = $filename;

        }
        $user->name = $request->name;
        $user->save();
        return response()->json(['message' => 'Profile updated successfully']);
    }


}
