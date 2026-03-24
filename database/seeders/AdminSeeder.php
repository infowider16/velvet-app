<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        Admin::create([
            'name'          => 'Super Admin',
            'email'         => 'admin@gmail.com',
            'password'      => Hash::make('123456'), // 🔒 Always hash passwords
            'profile_image' => null, // or 'default.png' if you have a default image
        ]);
    }
}
