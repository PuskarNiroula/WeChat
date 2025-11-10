<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastMessage extends Model
{
    protected $table='last_message';
    protected $guarded=[];
    public function conversation(){
        return $this->belongsTo(Conversation::class);
    }
    public function message(){
        return $this->belongsTo(Message::class);
    }
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Message::class,
            'id',        // Foreign key on Message table
            'id',        // Foreign key on User table
            'message_id',// Local key on LastMessage
            'sender_id'    // Local key on Message
        );
    }
}
