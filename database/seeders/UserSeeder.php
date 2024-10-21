<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
      
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@ephinmaed.com',
            'password' => bcrypt('admin123'),
            'user_type' => 'admin',
            'email_verified_at' => Carbon::now(), 
        ]);


        User::create([
            'name' => 'Faculty User',
            'email' => 'faculty@phinmaed.com',
            'password' => bcrypt('faculty123'),
            'user_type' => 'faculty',
            'email_verified_at' => Carbon::now(), 
        ]);

    }
    
}
