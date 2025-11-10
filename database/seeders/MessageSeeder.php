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
                'conversation_id' => 1,
                'sender_id' => 1,
                "message" => "Hello hy"
            ]);
            Message::create([
                'conversation_id' => 1,
                'sender_id' => 2,
                "message" => "Yes vanana"
            ]);
        Message::create([
            'conversation_id' => 1,
            'sender_id' => 1,
            "message" => "Oii Khaali xas?"
        ]);
        Message::create([
            'conversation_id' => 1,
            'sender_id' => 2,
            "message" => "Nai yrr"
        ]);
        Message::create([
            'conversation_id' => 3,
            'sender_id' => 2,
            "message" => "Oii sunana"
        ]);

    }
}
