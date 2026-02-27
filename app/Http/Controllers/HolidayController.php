<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Traits\LogsActions;
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
        ]);

        try {
            DB::beginTransaction();

            // 1. Fetch common metadata
            $typeName   = DB::table('info_master')->where('id', $request->holiday_type)->value('name');
            $officeName = $request->operational_office ? DB::table('operational_offices')->where('id', $request->operational_office)->value('name') : null;
            $deptName   = $request->department ? DB::table('departments')->where('id', $request->department)->value('department_name') : null;
            $divName    = $request->division ? DB::table('divisions')->where('id', $request->division)->value('division_name') : null;

            $start = \Carbon\Carbon::parse($request->date_from);
            $end   = \Carbon\Carbon::parse($request->date_to);

            $personIdArray = ($request->is_personwise && $request->total_person_ids)
                ? array_filter(array_map('trim', explode(',', $request->total_person_ids)))
                : [];

            $currentDate = $start->copy();
            $skippedDates = [];

            // 2. Iterate through each day
            while ($currentDate <= $end) {
                $formattedDate = $currentDate->format('Y-m-d');

                // 3. Check for existing record for this specific date and scope
                $exists = Holiday::where('holiday_date', $formattedDate)
                    ->where('operational_office', $request->operational_office)
                    ->where('department', $request->department)
                    ->where('division', $request->division)
                    ->when($request->is_personwise, function ($query) {
                        return $query->where('is_personwise', true);
                    })
                    ->exists();

                if ($exists) {
                    $skippedDates[] = $formattedDate;
                    $currentDate->addDay();
                    continue; // Skip to next day if record already exists
                }

                $holiday = Holiday::create([
                    'holiday_type'            => $request->holiday_type,
                    'holiday_type_name'       => $typeName,
                    'holiday_date'            => $formattedDate,
                    'date_range'              => 1,
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

                if (!empty($personIdArray)) {
                    $holiday->persons()->sync($personIdArray);
                }

                $currentDate->addDay();
            }

            DB::commit();

            $message = 'Holidays processed successfully.';
            if (!empty($skippedDates)) {
                $message .= ' Note: Some dates were skipped as they already exist: ' . implode(', ', $skippedDates);
                return back()->withErrors(['error' => 'Note: Some dates were skipped as they already exist: ' . implode(', ', $skippedDates)]);
            }

            return redirect('/holidays')->with('success', $message);

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
    public function destroy(Holiday $holiday, Request $request)
    {
        try {
            DB::beginTransaction();
            LogsActions::logDelete($holiday, $request->comments ?? 'Holiday deleted');
            $holiday->delete();
            DB::commit();
            return redirect()->route('holidays.index')
                ->with('success', 'Holiday deleted and logged successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('holidays.index')
                ->with('error', 'Failed to delete holiday: ' . $e->getMessage());
        }
    }
}
