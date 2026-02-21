<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Division;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display User Listing
     */
    public function index(Request $request): Response
    {
        $users = User::latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    /**
     * Show Create Form
     */
    public function create(): Response
    {
        return Inertia::render('users/form', [
            'employees'   => Employee::select('id', 'person_name as name')->get(),
            'divisions'    => Division::select('id', 'division_name')->get(),
            'departments'  => Department::select('id', 'department_name')->get(),
            'roles'       => ['Admin', 'HR Manager', 'Division Head', 'Department Head', 'User', 'Developer'],
        ]);
    }

    /**
     * Store User
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => ['required', 'confirmed', Password::defaults()],
            'employee_id'   => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'division_id'   => 'nullable|integer',
            'role'          => 'required|string',
            'status'        => 'required|string',
            'mobile_access' => 'required|boolean',
            'access_days'   => 'nullable|array',
        ]);

        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'employee_id'   => $request->employee_id,
            'department_id' => $request->department_id,
            'division_id'   => $request->division_id,
            'role'          => $request->role,
            'status'        => $request->status,
            'mobile_access' => $request->mobile_access,
            'access_days'   => $request->access_days,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show Edit Form
     */
    public function edit(User $user): Response
    {
        return Inertia::render('users/form', [
            'user'        => $user,
            'employees'   => Employee::select('id', 'person_name as name')->get(),
            'divisions'    => Division::select('id', 'division_name as name')->get(),
            'departments'  => Department::select('id', 'department_name as name')->get(),
            'roles'       => ['Admin', 'HR Manager', 'Division Head', 'Department Head', 'User', 'Developer'],
        ]);
    }

    /**
     * Update User
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'      => ['nullable', 'confirmed', Password::defaults()],
            'employee_id'   => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'division_id'   => 'nullable|integer',
            'role'          => 'required|string',
            'status'        => 'required|string',
            'mobile_access' => 'required|boolean',
            'access_days'   => 'nullable|array',
        ]);

        $data = $request->only([
            'name', 'email', 'employee_id', 'department_id',
            'division_id', 'role', 'status', 'mobile_access', 'access_days'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
