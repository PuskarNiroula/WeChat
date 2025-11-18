<?php

namespace App\Http\Controllers\Api;

use App\Exception\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\ConUser;
use App\Models\LastMessage;
use App\Service\MessageService;
use App\Service\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ExtraController extends Controller{
    protected UserService $userService;
    protected MessageService $messageService;
    public function __construct()
    {
        $this->userService=new userService();
        $this->messageService=new MessageService();
    }
    public function search(string $searchTerm):JsonResponse{
        if(trim($searchTerm)==""){
            return response()->json(['error' => 'Search term cannot be empty'], 400);
        }
     try{
       return response()->json($this->userService->searchUser($searchTerm),200);
     }catch (UserNotFoundException $e){
            return response()->json(['error' => $e->getMessage()], 404);
     }
    }
    public function getSidebar()
    {
        return response()->json($this->messageService->getSidebar());
    }







}
