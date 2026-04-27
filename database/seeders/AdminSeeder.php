<?php

namespace Database\Seeders;

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
        Admin::updateOrCreate(
            [
                'email' => 'admin@gmail.com' // unique identifier
            ],
            [
                'name'          => 'Super Admin',
                'password'      => Hash::make('123456'),
                'profile_image' => null,
            ]
        );
    }
}