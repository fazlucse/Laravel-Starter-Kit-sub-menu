<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $holidays = Holiday::with('employee')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        return Inertia::render('holidays/index', [
            'holidays' => $holidays
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $persons = Person::all(); // for multi-select

        return Inertia::render('holidays/create', [
            'persons' => $persons
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'holiday_type' => 'required|integer',
            'holiday_date' => 'required|date',
            'person_ids'   => 'array', // optional
        ]);

        $holiday = Holiday::create($request->except('person_ids'));

        // Sync persons (pivot table)
        if ($request->person_ids) {
            $holiday->persons()->sync($request->person_ids);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        $holiday->load('persons');

        return Inertia::render('holidays/show', [
            'holiday' => $holiday
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        $holiday->load('persons');
        $persons = Person::all();

        return Inertia::render('holidays/edit', [
            'holiday' => $holiday,
            'persons' => $persons
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'holiday_type' => 'required|integer',
            'holiday_date' => 'required|date',
            'person_ids'   => 'array', // optional
        ]);

        $holiday->update($request->except('person_ids'));

        // Update pivot table
        if ($request->person_ids) {
            $holiday->persons()->sync($request->person_ids);
        } else {
            $holiday->persons()->detach(); // remove all if empty
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
