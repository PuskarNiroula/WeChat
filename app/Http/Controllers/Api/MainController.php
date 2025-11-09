<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class MainController extends Controller
{

    public function dashboard(){
        return view('Main.dashboard');
    }

}
