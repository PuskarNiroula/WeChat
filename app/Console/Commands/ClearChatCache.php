<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearChatCache extends Command
{
    protected $signature = 'chat:clear-cache';
    protected $description = 'Clear all chat messages cached in Redis';

    public function handle()
    {
        $keys = Redis::keys('*');

        if (empty($keys)) {
            $this->info('No chat cache found.');
            return 0;
        }

        foreach ($keys as $key) {
            Redis::del($key);
        }

        $this->info('All chat cache cleared successfully!');
        return 0;
    }
}
