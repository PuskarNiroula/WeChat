<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;

class ChatCacheService
{
    protected int $limit=50;

    public function pushMessage(int $conversationId,string $message,int $sender_id, string $createdAt){
        $key="chat:message:{$conversationId}";
        $data=[
            'message'=>$message,
            'sender_id'=>$sender_id,
            'time'=>$createdAt==null?now():$createdAt
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
