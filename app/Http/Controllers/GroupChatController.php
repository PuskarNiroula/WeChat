<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GroupChatController extends Controller
{

    public function Index():View{
        return view('Main.GroupChat');
    }
    public function editGroupChat(int $groupChatId):View{
        return view('Main.EditGroupChat',compact('groupChatId'));
    }
    public function addMember(int $groupChatId):View{
        return view('Main.AddMember',compact('groupChatId'));
    }
    public function groupChatDetails(int $groupChatId):View{
        return view('Main.GroupChatDetails',compact('groupChatId'));
    }
    public function removeMember(int $groupChatId):View{
        return view('Main.RemoveChatMembers',compact('groupChatId'));
    }

}
