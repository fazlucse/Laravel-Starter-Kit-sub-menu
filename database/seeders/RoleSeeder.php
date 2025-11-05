<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Developer',
            'Super Admin',
            'Admin',
            'HR Admin',
            'HR Manager',
            'HR Officer',
            'HR Executive',
            'Employee',
            'Guest',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
