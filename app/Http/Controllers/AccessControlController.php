<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;
use App\Constants\RoleConstants;

class AccessControlController extends Controller
{
    public function index()
    {
        foreach (RoleConstants::ALL as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        return Inertia::render('access-control/AccessControl', [
            // Ensure we use the 'web' guard specifically
            'roles' => Role::whereIn('name', RoleConstants::ALL)
                ->where('guard_name', 'web')
                ->with('permissions')
                ->get()
                ->sortBy(fn($role) => array_search($role->name, RoleConstants::ALL))
                ->values(),
            'modules' => [
                ['name' => 'Employee Management', 'slug' => 'employee'],
                ['name' => 'Movement Register', 'slug' => 'movement'],
                ['name' => 'Attendance System', 'slug' => 'attendance'],
                ['name' => 'Leave Requests', 'slug' => 'leave-request'],
                ['name' => 'Leave Allotment', 'slug' => 'leave-allotment'], // Added missing module
                ['name' => 'Payroll System', 'slug' => 'payroll'],
                ['name' => 'Holiday Setup', 'slug' => 'holiday'],
                ['name' => 'Report System', 'slug' => 'reports'], // Added for settings/reports access
            ],
            'actions' => ['view', 'create', 'edit', 'delete', 'approve']
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $permissionNames = $request->input('permissions', []);
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }
        $role->syncPermissions($permissionNames);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        return back()->with('success', "Permissions for {$role->name} updated successfully.");
    }
}
