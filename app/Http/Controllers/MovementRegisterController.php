<?php

namespace App\Http\Controllers;

use App\Models\MovementRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;


class MovementRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $isManager = \App\Models\Employee::where('reporting_manager_id', $user->employee_id)
            ->exists();
        $canApprove = $user->can('movement.approve') || $isManager;

        $perPage = (int) $request->query('per_page', 10);
        $movements = MovementRegister::visibleTo(auth()->user(),'movement')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('movement-register/index', [
            'movements' => $movements,
            'canApprove'=>$canApprove
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Find if user has a trip that hasn't ended yet to show the "End" form
        $activeMovement = MovementRegister::where('created_by', Auth::id())
            ->whereNull('movement_ended_at')
            ->latest()
            ->first();

        return Inertia::render('movement-register/create', [
            'activeMovement' => $activeMovement,
        ]);
    }

    /**
     * START TRIP: Store the basic info (Store Function)
     */
    public function store(Request $request)
    {
        // Validation exactly as you requested for the STARTING move
        $request->validate([
            'from.type' => 'required|string',
            'from.factory' => 'nullable|integer',
            'from.sub_factory' => 'nullable|integer',
            'from.new_name' => 'nullable|string',

            'to.type' => 'required|string',
            'to.factory' => 'nullable|integer',
            'to.sub_factory' => 'nullable|integer',
            'to.new_name' => 'nullable|string',

            'purpose' => 'required|array|min:1',
        ]);

        $movement = new MovementRegister();

        // Origin mapping
        $from = $request->input('from');
        $movement->origin_location_type = $from['type'];
        // Destination mapping
        $to = $request->input('to');
        $movement->destination_location_type = $to['type'];
        $from = $request->input('from');
        $movement->origin_location_type = $request->input('from.type');
        $movement->origin_location_id = $from['factory']
            ?? $from['sub_factory']
            ?? 0;
        $movement->origin_location_name = $from['new_name']
            ?? $from['factory_name']
            ?? $from['sub_factory_name']
            ?? $from['type'];

        // Destination mapping
        $to = $request->input('to');
        $movement->destination_location_type = $to['type'] ?? 'Other';

        // Extract ID
        $movement->destination_location_id = $to['factory']
            ?? $to['sub_factory']
            ?? 0;

        // Extract Name
        $movement->destination_location_name = $to['new_name']
            ?? $to['factory_name']
            ?? $to['sub_factory_name']
            ?? $from['type'];
        // Purpose (Array to String)
        $movement->purpose = $request->purpose;
        // Initial Metadata
        $movement->movement_started_at = now();
        $movement->created_by = Auth::id();
        $movement->created_by_name = Auth::user()?->name;
        $movement->employee_id = Auth::user()?->employee_id;
        $movement->entry_from = 'web';
        $movement->save();
        return redirect()->route('movement-registers.create')->with('success', 'Movement started successfully');
    }

    /**
     * FINISH TRIP: Update with transport, visit details, and bills (Update Function)
     */
    public function update(Request $request, MovementRegister $movementRegister)
    {
        // =============================
        // BASIC VALIDATION
        // =============================
        $request->validate([
            'means_of_transport' => 'required|array|min:1',
            'visit_for'          => 'required|string',
            'customer_id'        => 'required_if:visit_for,Customer|array',
            'development'        => 'required_if:visit_for,Development|nullable|string',
            'others'             => 'required_if:visit_for,Others|nullable|string',
            'conveyance_bill'    => 'required|numeric',
            'budgeted_bill'      => 'nullable|numeric',
        ]);

        // =============================
        // TRANSPORT MODE
        // =============================
        $movementRegister->transport_mode = $request->means_of_transport;

        // =============================
        // VISIT TYPE LOGIC
        // =============================
        $movementRegister->visit_type = $request->visit_for;


        if ($request->visit_for === 'Customer') {
            $customers = $request->customer_id;
            // If array of objects
            if (is_array($customers) && isset($customers[0]['id'])) {
                $movementRegister->customer_id = implode(',', collect($customers)->pluck('id')->toArray());
                $movementRegister->customer_name = implode(', ', collect($customers)->pluck('name')->toArray());
                $movementRegister->customer_obj = $customers;
            }
            // If array of IDs only
            else {
                $movementRegister->customer_id = implode(',', $customers);
                $movementRegister->customer_name = null;
                $movementRegister->customer_obj = $customers;
            }
//            $movementRegister->bill_remarks = null;
        } elseif ($request->visit_for === 'Development') {
            $movementRegister->customer_name = $request->development;
            $movementRegister->customer_id = null;
            $movementRegister->customer_obj = null;

        } else if($request->visit_for === 'Others') {
            $movementRegister->customer_name = $request->others;
            $movementRegister->customer_id = null;
            $movementRegister->customer_obj = null;
        }

        // =============================
        // BILLING
        // =============================
        $movementRegister->conveyance_amount = $request->conveyance_bill;
        $movementRegister->budgeted_amount = $request->budgeted_bill ?? 0;

        // =============================
        // FINISH MOVEMENT
        // =============================
        if ($request->is_finishing) {
            $movementRegister->movement_ended_at = now();
        }

        // =============================
        // START MOVEMENT (IF NOT FINISHING)
        // =============================
        if (!$request->is_finishing) {

            $request->validate([
                'from.type'        => 'required|string',
                'from.factory'     => 'nullable|integer',
                'from.sub_factory' => 'nullable|integer',
                'from.new_name'    => 'nullable|string',

                'to.type'          => 'required|string',
                'to.factory'       => 'nullable|integer',
                'to.sub_factory'   => 'nullable|integer',
                'to.new_name'      => 'nullable|string',

                'purpose'          => 'required|array|min:1',
            ]);

            $from = $request->input('from');
            $to   = $request->input('to');

            // Origin
            $movementRegister->origin_location_type = $from['type'];
            $movementRegister->origin_location_id = $this->resolveLocationId($from['type'], $from);
            $movementRegister->origin_location_name = $this->resolveLocationName($from['type'],$from);

            // Destination
            $movementRegister->destination_location_type = $to['type'];
            $movementRegister->origin_location_id = $this->resolveLocationId($to['type'], $to);
            $movementRegister->destination_location_name = $this->resolveLocationName($to['type'],$to);
            $movementRegister->purpose = $request->purpose;
            $movementRegister->movement_started_at = now();
            $movementRegister->updated_by = Auth::id();
            $movementRegister->entry_from = 'web';
        }

        // =============================
        // SAVE
        // =============================
        $movementRegister->save();

        return redirect()
            ->route('movement-registers.index')
            ->with('success', 'Movement updated successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show(MovementRegister $movementRegister): Response
    {
        return Inertia::render('movement-register/show', [
            'movement' => $movementRegister,
        ]);
    }

    /**
     * Show the form for editing (Optional, if you need a standard edit)
     */
    public function edit(MovementRegister $movementRegister): Response
    {
        return Inertia::render('movement-register/edit', [
            'movement' => $movementRegister,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MovementRegister $movementRegister)
    {
        $movementRegister->delete();

        return redirect()
            ->route('movement-registers.index')
            ->with('success', 'Movement deleted successfully');
    }
    /**
     * Print selected movement records.
     */
    // MovementRegisterController.php

    public function print(Request $request)
    {
        // 1. Get IDs from URL (Remove the 'return' from this line)
        $idsString = $request->query('ids');
        if (!$idsString) return redirect()->back();
        $ids = explode(',', $idsString);
        // 2. Fetch records
        $movements = MovementRegister::whereIn('id', $ids)
            ->orderBy('movement_started_at', 'desc')
            ->get();
        // 3. Return the Blade view
        return view('print.movement-report', [
            'movements' => $movements,
            'printedBy' => auth()->user()->name,
            'printDate' => now()->format('d-M-Y h:i A')
        ]);
    }
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|in:2,22',
            'reason' => 'nullable|string|max:500'
        ]);

        $data = [
            'status' => $request->status,
            'approved_by' => auth()->id(),
            'approved_by_name' => auth()->user()->name,
            'approved_at' => now(),
        ];

        // If it's a rejection, save the reason
        if ($request->status == 22) {
            $data['rejection_reason'] = $request->reason;
        }

        $updatedCount = MovementRegister::whereIn('id', $request->ids)
            ->where('status', 0)
            ->update($data);

        $msg = $request->status == 2 ? 'Approved' : 'Rejected';
        return redirect()->back()->with('success', "Successfully $msg $updatedCount movements.");
    }
    public function getSettings()
    {
        // Fetch generic master data from InfoMaster model
        $purposes = \App\Models\InfoMaster::where('type', 'Movement Purpose')
            ->select('id', 'name')
            ->get();

        $transports = \App\Models\InfoMaster::where('type', 'Transport Mode')
            ->select('id', 'name')
            ->get();

        // Fetch company data filtered by type
        $companies = \App\Models\Company::select('id', 'name', 'type')->get();

        return response()->json([
            'factories'     => $companies->where('type', 'Factory')->values(),
            'sub_factories' => $companies->where('type', 'Sub Factory')->values(),
            'purposes'      => $purposes,
            'transports'    => $transports,
            'customers'     =>  $companies->where('type', 'Customer')->values(),
        ]);
    }
    private function resolveLocationName($type,array $location): string
    {
        if (!isset($type)) {
            return '';
        }
        switch ($type) {

            case 'Factory':
                return $location['factory_name'] ?? '';

            case 'Sub Factory':
                return $location['sub_factory_name'] ?? '';

            case 'Other':
                return $location['new_name'] ?? '';
            default:
                return $location['type'];
        }
    }
    private function resolveLocationId($type, array $location): int
    {
        if (!isset($type)) {
            return 0;
        }

        switch ($type) {

            case 'Factory':
                return $location['factory'] ?? 0;

            case 'Sub Factory':
                return $location['sub_factory'] ?? 0;

            case 'Other':
                return 0;

            case 'Home Office':
                return 0;

            default:
                return 0;
        }
    }
}
