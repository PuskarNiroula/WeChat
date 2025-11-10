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
                'name'=>"Puskar Niroula",
                "email"=>"puskar@gmail.com",
                "password"=>"password",
            ],
            [
                'name'=>"Samana Dahal",
                "email"=>"samana@gmail.com",
                "password"=>"password",
            ],
            [
                'name'=>"Ram Bahadur",
                "email"=>"ram@gmail.com",
                "password"=>"password",
            ],
            [
                'name'=>"Sita Kumari",
                "email"=>"sita@gmail.com",
                "password"=>"password",
            ],


        ];
        foreach ($users as $user){
            User::create($user);
        }

    }
}
