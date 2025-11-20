<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;

class ChatCacheService
{
    protected int $limit=50;

    public function pushMessage(int $conversationId,string $message){
        $key="chat:message:{$conversationId}";
        Redis::lpush($key,json_encode($message));
        Redis::ltrim($key,0,$this->limit-1);
    }

    public function getMessage(int $conversationId){
        $key="chat:message:{$conversationId}";
        $message=Redis::lrange($key,0,$this->limit-1);

        return array_map(fn($msg)=>json_decode($msg,true),$message);

    }

}
