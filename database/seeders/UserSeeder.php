<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Student User',
            'email' => 'frdo.opu-an.up@phinmaed.com',
            'password' => bcrypt('France123'),
            'user_type' => 'student'
        ]);
    }
}
