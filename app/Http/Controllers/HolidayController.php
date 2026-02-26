<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);

        // Load with persons pivot as defined in your migration context
        $holidays = Holiday::with('persons')
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
        // Fetch masters for the dropdowns

        $holiday_types = \App\Models\InfoMaster::where('type', 'Holiday Type')
            ->select('id', 'name')
            ->get();
        return Inertia::render('holidays/create', [
            'offices' => [],
            'departments' => DB::table('departments')->select('id', 'department_name as name')->get(),
            'divisions' => DB::table('divisions')->select('id', 'division_name as name')->get(),
//            'workgroups' => DB::table('workgroups')->select('id', 'name')->get(),
            'holidayTypes' => $holiday_types,//DB::table('holiday_types')->select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'holiday_type' => 'required|integer',
            'date_from'    => 'required|date',
            'date_to'      => 'required|date|after_or_equal:date_from',
            'is_personwise'=> 'boolean',
            'total_person_ids' => 'required_if:is_personwise,true|nullable|string',
        ], [
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
        ]);

        try {
            DB::beginTransaction();

            // 1. Prepare common data (Fetch names once outside the loop for performance)
            $typeName = DB::table('info_master') // Assuming plural based on your previous error
            ->where('id', $request->holiday_type)
                ->where('type', 'Holiday Type')
                ->value('name');

            $officeName = $request->operational_office
                ? DB::table('operational_offices')->where('id', $request->operational_office)->value('name')
                : null;

            $deptName = $request->department
                ? DB::table('departments')->where('id', $request->department)->value('department_name')
                : null;

            $divName = $request->division
                ? DB::table('divisions')->where('id', $request->division)->value('division_name')
                : null;

            // 2. Setup Date Range Loop
            $start = \Carbon\Carbon::parse($request->date_from);
            $end = \Carbon\Carbon::parse($request->date_to);

            // Prepare person IDs array once if personwise
            $personIdArray = [];
            if ($request->is_personwise && $request->total_person_ids) {
                $personIdArray = array_filter(array_map('trim', explode(',', $request->total_person_ids)));
            }

            // 3. Iterate through each day
            $currentDate = $start->copy();
            while ($currentDate <= $end) {

                $holiday = Holiday::create([
                    'holiday_type'            => $request->holiday_type,
                    'holiday_type_name'       => $typeName,
                    'holiday_date'            => $currentDate->format('Y-m-d'),
                    'date_range'              => 1, // Each record represents exactly 1 day
                    'is_personwise'           => $request->is_personwise ?? false,
                    'total_person_ids'        => $request->is_personwise ? implode(',', $personIdArray) : null,
                    'operational_office'      => $request->operational_office,
                    'operational_office_name' => $officeName,
                    'department'              => $request->department,
                    'department_name'         => $deptName,
                    'division'                => $request->division,
                    'division_name'           => $divName,
                    'remarks'                 => $request->remarks,
                ]);

                // Sync pivot table for each day if personwise
                if (!empty($personIdArray)) {
                    $holiday->persons()->sync($personIdArray);
                }
                $currentDate->addDay(); // Move to next day
            }
            DB::commit();
            return redirect('/holidays')->with('success', 'Holidays created successfully for the selected range.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Database Error: ' . $e->getMessage()])->withInput();
        }
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

        return Inertia::render('holidays/edit', [
            'holiday' => $holiday,
            'persons' => Person::all(),
            'offices' => DB::table('operational_offices')->select('id', 'name')->get(),
            'departments' => DB::table('departments')->select('id', 'name')->get(),
            'divisions' => DB::table('divisions')->select('id', 'name')->get(),
            'workgroups' => DB::table('workgroups')->select('id', 'name')->get(),
            'holidayTypes' => DB::table('holiday_types')->select('id', 'name')->get(),
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
            'person_ids'   => 'array|nullable',
        ]);

        $data = $request->except('person_ids');

        // Re-sync names in case IDs changed
        $data['holiday_type_name'] = DB::table('holiday_types')->where('id', $request->holiday_type)->value('name');
        $data['operational_office_name'] = DB::table('operational_offices')->where('id', $request->operational_office)->value('name');
        $data['department_name'] = DB::table('departments')->where('id', $request->department)->value('name');
        $data['division_name'] = DB::table('divisions')->where('id', $request->division)->value('name');
        $data['workgroup_name'] = DB::table('workgroups')->where('id', $request->workgroup)->value('name');

        if ($request->is_personwise && $request->person_ids) {
            $data['total_person_ids'] = implode(',', $request->person_ids);
        } else {
            $data['total_person_ids'] = null;
        }

        $holiday->update($data);

        // Update pivot table
        if ($request->person_ids) {
            $holiday->persons()->sync($request->person_ids);
        } else {
            $holiday->persons()->detach();
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        // Detach persons first to maintain integrity
        $holiday->persons()->detach();
        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
