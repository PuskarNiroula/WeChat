<?php

namespace Database\Seeders;

use App\Models\LastMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LastMessage::create([
            "conversation_id"=>1,
            "message_id"=>2,
        ]);
        LastMessage::create([
            "conversation_id"=>2,
            "message_id"=>3,
        ]);
        LastMessage::create([
            "conversation_id"=>3,
            "message_id"=>5,
        ]);
    }
}
