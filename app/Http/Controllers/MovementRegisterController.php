<?php

namespace App\Http\Controllers;

use App\Models\MovementRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        $data = $request->validate([
            'purpose' => 'required|string',
            'transport_mode' => 'nullable|string',
        ]);

        MovementRegister::create($data);

        return redirect()
            ->route('movement-registers.index')
            ->with('success', 'Movement added successfully');
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
