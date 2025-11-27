<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $perPage = (int) $request->query('per_page', 10);
    $search = $request->input('search');

    $query = Attendance::with(['employee']); // eager load employee relationship

    // Filters
    $filters = $request->only(['emp_name', 'employee_id', 'department_name', 'designation_name']);

    foreach ($filters as $field => $value) {
        if (!empty($value)) {
            $query->where($field, 'like', "%{$value}%");
        }
    }

    // Optional global search
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('emp_name', 'like', "%{$search}%")
              ->orWhere('employee_id', 'like', "%{$search}%")
              ->orWhereHas('employee', function ($q2) use ($search) {
                  $q2->where('person_name', 'like', "%{$search}%");
              });
        });
    }

    $attendance = $query->orderByDesc('add_time')
                        ->paginate($perPage)
                        ->appends($request->query());

    return Inertia::render('attendance/index', [
        'attendance' => $attendance,
        'perPage' => $perPage,
        'defaultPerPage' => 15,
        'search' => $search,
        'filters' => $filters,
        'flash' => [
            'success' => session('success'),
        ],
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
