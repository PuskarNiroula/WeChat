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
        ConUser::create([
            "conversation_id"=>1,
            "user_id"=>1,
        ]);
        ConUser::create([
            "conversation_id"=>1,
            "user_id"=>2,
        ]);
    }
}
