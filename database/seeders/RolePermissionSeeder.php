<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------
        // Create Permissions (all modules)
        // -------------------------
        $permissions = [
            // Person Module
            'person.create', 'person.edit', 'person.delete', 'person.view',
            // HR Module
            // 'hr.view', 'hr.create', 'hr.edit', 'hr.delete', 'hr.manage',
            // Payroll Module
            // 'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete', 'payroll.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // -------------------------
        // Create Roles & assign permissions
        // -------------------------
        $roles = [
            'Developer' => $permissions, // all permissions
            'Super Admin' => $permissions, // all permissions
            'Admin' => ['person.create','person.edit','person.delete','person.view'],
            // 'HR Manager' => ['hr.view','hr.edit','hr.manage','person.view'],
            // 'Payroll Manager' => ['payroll.view','payroll.edit','payroll.manage','person.view'],
            'Employee' => ['person.view'],
            // 'Guest' => ['person.view'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        echo "Roles and permissions seeded successfully.\n";
    }
}
