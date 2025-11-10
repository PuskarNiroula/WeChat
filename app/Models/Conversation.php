<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $guarded=[];

    public function conUsers(){
     return $this->hasMany(ConUser::class,"conversation_id",'id');
    }
    public function messages(){
      return  $this->hasMany(Message::class);
    }
    public function lastMessage(){
      return  $this->hasOne(LastMessage::class);
    }

}
