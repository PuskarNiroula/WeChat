<?php

namespace Database\Seeders;

use App\Models\ConUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoversationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversation_id=4;
        $user_id=5;
        for($i=0;$i<15;$i++){
            ConUser::create([
                "conversation_id"=>$conversation_id,
                "user_id"=>2,
            ]);
            ConUser::create([
                "conversation_id"=>$conversation_id,
                "user_id"=>$user_id,
            ]);
            $user_id++;
            $conversation_id++;
        }

    }
}
