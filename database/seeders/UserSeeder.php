<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'name' => 'Developer',
                'email' => 'test@t.com',
                'password' => Hash::make('12345678'),
                'created_by' => null, // first user, created by system
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@t.com',
                'password' => Hash::make('12345678'),
                'created_by' => 1, // created by Admin
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'admin@t.com',
                'password' => Hash::make('12345678'),
                'created_by' => 1,
            ],
              [
                'name' => 'Jane Smith',
                'email' => 'hr@t.com',
                'password' => Hash::make('12345678'),
                'created_by' => 1,
            ],
             [
                'name' => 'Jane Smith',
                'email' => 'employee@t.com',
                'password' => Hash::make('12345678'),
                'created_by' => 1,
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password']
            ]);
        }
    }
}
