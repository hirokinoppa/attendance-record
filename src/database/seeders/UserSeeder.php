<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | 一般ユーザー10人
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'スタッフ' . $i,
                'email' => 'staff' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'general',
                'email_verified_at' => now(),
            ]);
        }
    }
}