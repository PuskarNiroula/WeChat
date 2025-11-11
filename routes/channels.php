<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('Message-Channel.{id}', function ($user, $id) {
    Log::info('Authenticated user: ' . json_encode($user));
    return $user && (int) $user->id === (int) $id;
});

