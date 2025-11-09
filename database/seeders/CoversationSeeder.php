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
          for($i=1;$i<16;$i++){
              Conversation::create([
                  'type'=>"private"
              ]);
          }

    }
}
