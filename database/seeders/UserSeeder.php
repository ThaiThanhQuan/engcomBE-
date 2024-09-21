<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();

        $user->name = "User5";
        $user->email = "xxxx@gmail.com";
        $user->password = Hash::make("123456");
        $user->role_id= "3";

   
        $user->save();
    }
}
