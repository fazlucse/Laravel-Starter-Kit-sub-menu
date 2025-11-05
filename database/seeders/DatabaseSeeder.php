<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run multiple seeders together
        $this->call([
            UserSeeder::class,     
            InfoMasterSeeder::class, 
            RoleSeeder::class,
            UserRoleSeeder::class,
            RolePermissionSeeder::class,
            PersonSeeder::class
        ]);
    }
}
