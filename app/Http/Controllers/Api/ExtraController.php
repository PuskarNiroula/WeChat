<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ExtraController extends Controller{
    public function search(string $searchTerm):JsonResponse{
        if(trim($searchTerm)==""){
            return response()->json(['error' => 'Search term cannot be empty'], 400);
        }
        $users=User::where('name','like','%'.$searchTerm.'%')
            ->limit(10)
            ->get()
            ->pluck('name');
        return response()->json($users,200);
    }
}
