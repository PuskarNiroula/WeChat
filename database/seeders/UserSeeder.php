<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users=[
            [
                'name'=>"Ram Bahadur",
                "email"=>"ram@gmail.com",
                "password"=>"Password@123"
            ],
            [
                'name'=>"Sita Kumari",
                "email"=>"sita@gmail.com",
                "password"=>"Password@123"

            ]
        ];
        foreach ($users as $user){
            User::create($user);
        }

    }
}
