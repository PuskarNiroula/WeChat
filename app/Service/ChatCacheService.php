<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;

class ChatCacheService
{
    protected int $limit=50;

    public function pushMessage(int $conversationId,string $message,$iv,int $sender_id, string $createdAt,int $key_version,string $avatar){
        $key="chat:message:{$conversationId}";
        $data=[
            'message'=>$message,
            'sender_id'=>$sender_id,
            'iv'=>$iv,
            'time'=>$createdAt==null?now():$createdAt,
            'key_version'=>$key_version,
            'avatar'=>$avatar
        ];
        Redis::lpush($key,json_encode($data));
        Redis::ltrim($key,0,$this->limit-1);
    }

    public function getMessage(int $conversationId){
        $key="chat:message:{$conversationId}";
        $message=Redis::lrange($key,0,$this->limit-1);

        return array_map(fn($msg)=>json_decode($msg,true),$message);

    }

}
