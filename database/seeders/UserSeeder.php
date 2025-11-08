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
                "password"=>"Password@123"
            ],
            [
                'name'=>"Samana Dahal",
                "email"=>"samana@gmail.com",
                "password"=>"Password@123"

            ]
        ];
        foreach ($users as $user){
            User::create($user);
        }

    }
}
