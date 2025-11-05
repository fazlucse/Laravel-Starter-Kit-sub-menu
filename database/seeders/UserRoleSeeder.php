<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // ------------------------
        // Create roles if not exist
        // ------------------------
        $roles = ['Developer', 'Super Admin', 'Admin', 'HR Manager', 'Payroll Manager', 'Employee', 'Guest'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
        // ------------------------
        // Assign roles to existing users
        // ------------------------
        $users = User::all(); // select all users, you can filter if needed

        foreach ($users as $user) {
            // Example logic: assign role based on email or other criteria
            if (str_contains($user->email, 'test')) {
                $user->syncRoles(['Developer']);
            } elseif (str_contains($user->email, 'superadmin')) {
                $user->syncRoles(['Super Admin']);
            } elseif (str_contains($user->email, 'admin')) {
                $user->syncRoles(['Admin']);
            } elseif (str_contains($user->email, 'hr')) {
                $user->syncRoles(['HR Manager']);
            } elseif (str_contains($user->email, 'payroll')) {
                $user->syncRoles(['Payroll Manager']);
            } elseif (str_contains($user->email, 'employee')) {
                $user->syncRoles(['Employee']);
            } else {
                $user->syncRoles(['Guest']); // default role
            }
        }
        echo "Roles assigned to existing users successfully.\n";
    }
}
