<?php

namespace Database\Seeders;

use App\Models\conversation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            conversation::create([
                'type'=>"private"
        ]);

    }
}
