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
        // Define Permissions
        // -------------------------
        $permissions = [
            'person.create','person.edit','person.delete','person.view',
            'employee.view','employee.create','employee.edit','employee.delete',
            'attendance.view','attendance.create','attendance.edit','attendance.delete',
            'leave-request.view','leave-request.create','leave-request.edit','leave-request.delete',
            'leave-allotment.view','leave-allotment.create','leave-allotment.edit','leave-allotment.delete',
            'payroll.view','payroll.create','payroll.edit','payroll.delete',
            'holiday.view','holiday.create','holiday.edit','holiday.delete',
            'movement.view','movement.create','movement.edit','movement.delete',
            'reports.view',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // -------------------------
        // Define Roles & assign permissions
        // -------------------------
        $roles = [
            'Developer' => $permissions,
            'Super Admin' => $permissions,
            'Admin' => [
                'person.create','person.edit','person.delete','person.view',
                'employee.view','employee.create','employee.edit','employee.delete',
                'attendance.view','attendance.create','attendance.edit','attendance.delete',
                'leave-request.view','leave-request.create','leave-request.edit','leave-request.delete',
                'leave-allotment.view','leave-allotment.create','leave-allotment.edit','leave-allotment.delete',
                'payroll.view','payroll.create','payroll.edit','payroll.delete',
                'holiday.view','holiday.create','holiday.edit','holiday.delete',
                'movement.view','movement.create','movement.edit','movement.delete',
                'reports.view',
            ],
            'HR Manager' => [
                'person.view','person.create','person.edit','person.delete',
                'employee.view','employee.create','employee.edit','employee.delete',
                'attendance.view','attendance.create','attendance.edit',
                'leave-request.view','leave-request.create','leave-request.edit',
                'leave-allotment.view','leave-allotment.create','leave-allotment.edit',
                'holiday.view','holiday.create','holiday.edit',
            ],
            'Payroll Manager' => [
                'payroll.view','payroll.create','payroll.edit','payroll.delete',
                'reports.view','employee.view',
            ],
            'Employee' => [
                'person.view',
                'leave-request.view','leave-request.create',
                'attendance.view',
            ],
            'Guest' => [
                'person.view','employee.view',
            ],
        ];

        // Create roles if they don't exist, then assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        echo "Roles and permissions seeded successfully.\n";
    }
}
