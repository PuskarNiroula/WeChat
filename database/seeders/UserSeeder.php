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


      for($i=1;$i<16;$i++){
          User::create([
              "name"=>"Ram-".$i,
              "email"=>"ram$i@gmail.com",
              "password"=>"Password@123"
          ]);
      }

    }
}
