<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConUser extends Model
{
    protected $guarded=[];
    protected $table='conversation_user';


    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function messages(){
        return $this->hasMany(Message::class);
    }
    public function lastMessage(){
        return $this->hasOne(LastMessage::class);
    }


}
