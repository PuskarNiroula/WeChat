<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('Message-Channel.{id}', function ($user, $id) {
    Log::info('Channel auth attempt', ['user_id' => $user?->id, 'channel_id' => $id]);
    return $user && (int) $user->id === (int) $id;
});

