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
        Message::create([
            'conversation_id'=>3,
            'sender_id'=>3,
            "message"=>"How are you? "
        ]);
        Message::create([
            'conversation_id'=>3,
            'sender_id'=>4,
            "message"=>"I am Fine what about you?"
        ]);
    }
}
