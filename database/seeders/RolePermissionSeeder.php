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
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
         // Leave Request Module
            'leave-request.view', 'leave-request.create', 'leave-request.edit', 'leave-request.delete',

            // Leave Allotment
            'leave-allotment.view', 'leave-allotment.create', 'leave-allotment.edit', 'leave-allotment.delete',

            // Payroll Module
            'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete',

            // Holiday Management
            'holiday.view', 'holiday.create', 'holiday.edit', 'holiday.delete',

            // Movement Register
            'movement.view', 'movement.create', 'movement.edit', 'movement.delete',

            // Reports
            'reports.view',
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
            'Admin' => ['person.create','person.edit','person.delete','person.view', 'employee.view', 'employee.create', 'employee.edit', 'employee.delete',],
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
