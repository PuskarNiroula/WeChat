<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GroupChatController extends Controller
{

    public function Index():View{
        return view('Main.GroupChat');
    }

}
