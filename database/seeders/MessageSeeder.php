<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversation_id=4;
        $user_id=5;
        for($i=0;$i<15;$i++) {
            Message::create([
                'conversation_id' => $conversation_id,
                'sender_id' => 2,
                "message" => "Test message 1 "
            ]);
            Message::create([
                'conversation_id' => $conversation_id,
                'sender_id' => $user_id,
                "message" => "Test Reply 2"
            ]);
            $user_id++;
            $conversation_id++;
        }
    }
}
