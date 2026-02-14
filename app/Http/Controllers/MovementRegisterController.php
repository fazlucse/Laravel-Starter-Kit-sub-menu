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
    public function index(): Response
    {
        $movements = MovementRegister::latest()->paginate(20);

        return Inertia::render('movement-register/index', [
            'movements' => $movements,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('movement-register/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'from.type' => 'required|string',
            'from.factory' => 'nullable|integer',
            'from.sub_factory' => 'nullable|integer',
            'from.new_name' => 'nullable|string',

            'to.type' => 'required|string',
            'to.factory' => 'nullable|integer',
            'to.sub_factory' => 'nullable|integer',
            'to.new_name' => 'nullable|string',

            'purpose' => 'required|array|min:1',
            'means_of_trandport' => 'required|array|min:1',

            'visit_for' => 'required|string',
            'customer_id' => 'required_if:visit_for,Customer|array',
            'development' => 'required_if:visit_for,Development|nullable|string',
            'others' => 'required_if:visit_for,Others|nullable|string',

            'budgeted_bill' => 'nullable|numeric',
            'conveyance_bill' => 'nullable|numeric',
        ]);

        // 2. Map Frontend Object to Flat Database Columns
        $movement = new MovementRegister();
        $from = $request->input('from');
        // Origin mapping
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

        // Handle Arrays (Convert to comma-separated strings for TEXT/VARCHAR columns)
        // Note: Assuming 'purpose' and 'means_of_trandport' contain objects with a 'name' key
        $movement->purpose        = implode(', ', collect($request->purpose)->pluck('name')->toArray());
        $movement->transport_mode = implode(', ', collect($request->means_of_trandport)->pluck('name')->toArray());

        // Visit Logic
        $movement->visit_type     = $request->visit_for;
        if ($request->visit_for === 'Customer') {
            $movement->customer_id   = implode(',', collect($request->customer_id)->pluck('id')->toArray());
            $movement->customer_name = implode(', ', collect($request->customer_id)->pluck('name')->toArray());
        } elseif ($request->visit_for === 'Development') {
            $movement->bill_remarks = "Development: " . $request->development;
        } else {
            $movement->bill_remarks = "Other: " . $request->others;
        }

        // Amounts
        $movement->budgeted_amount  = $request->budgeted_bill ?? 0;
        $movement->conveyance_amount = $request->conveyance_bill ?? 0;

        // Metadata
        $movement->created_by      = Auth::id() ?? 0;
        $movement->created_by_name = Auth::user()?->name;
        $movement->entry_from      = 'web';

        $movement->save();

        return redirect()
            ->route('movement-registers.index')
            ->with('success', 'Movement registered successfully');
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
     * Show the form for editing the specified resource.
     */
    public function edit(MovementRegister $movementRegister): Response
    {
        return Inertia::render('movement-register/edit', [
            'movement' => $movementRegister,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MovementRegister $movementRegister)
    {
        $movementRegister->update($request->all());

        return redirect()
            ->route('movement-registers.index')
            ->with('success', 'Movement updated successfully');
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
}
