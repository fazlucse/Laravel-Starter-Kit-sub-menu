<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class AccessControlController extends Controller
{
    public function index()
    {
        return Inertia::render('access-control/AccessControl', [
            'roles' => Role::with('permissions')->get(),
            'modules' => [
                ['name' => 'Employee Management', 'slug' => 'employee'],
                ['name' => 'Movement Register', 'slug' => 'movement'],
                ['name' => 'Attendance System', 'slug' => 'attendance'],
                ['name' => 'Leave Requests', 'slug' => 'leave-request'],
                ['name' => 'Payroll System', 'slug' => 'payroll'],
                ['name' => 'Holiday Setup', 'slug' => 'holiday'],
            ],
            'actions' => ['view', 'create', 'edit', 'delete', 'approve']
        ]);
    }

    public function update(Request $request, Role $role) // Fixed the missing $
    {
        // syncPermissions expects an array of permission names
        $role->syncPermissions($request->permissions);

        return back()->with('success', 'Permissions updated successfully.');
    }
}
