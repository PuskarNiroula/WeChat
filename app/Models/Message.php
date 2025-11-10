<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded=[];
 public function user(){
     return $this->belongsTo(User::class,'sender_id','id');
 }
 public function conversation(){
     return $this->belongsTo(Conversation::class);
 }
 public function lastMessage(){
     return $this->hasOne(LastMessage::class);
 }
}
