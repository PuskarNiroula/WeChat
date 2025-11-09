<?php

namespace Database\Seeders;

use App\Models\Conversation;
use Illuminate\Database\Seeder;

class CoversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            Conversation::create([
                'type'=>"private"
        ]);

    }
}
