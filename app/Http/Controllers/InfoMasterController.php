<?php

namespace App\Http\Controllers;

use App\Models\InfoMaster;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InfoMasterController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'country'); // Default to country
        $perPage = (int) $request->query('per_page', 10);
        $entries = InfoMaster::
            latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('info-master/Index', [
            'entries' => $entries,
            'currentType' => $type,
            // Pass parents if we are adding something like a 'City' (needs a country)
            'parents' => InfoMaster::where('type', $this->getParentType($type))->get(['id', 'name']),
        ]);
    }
    public function create(Request $request)
    {
        $type = $request->query('type', 'country');

        return Inertia::render('info-master/Create', [
            'currentType' => $type,
            'masterTypes' => \App\Models\InfoMaster::distinct()->pluck('type'),
            'parents' => $this->getAvailableParents($type),
            'parentLabel' => $this->getParentLabel($type)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:info_master,id',
        ]);

        $validated['created_by'] = auth()->id();

        InfoMaster::create($validated);

        return redirect('/info-masters?type=' . $request->type)
            ->with('success', 'Entry created successfully.');
    }
    public function edit(InfoMaster $infoMaster)
    {
        // The type is taken directly from the record being edited
        $type = $infoMaster->type;

        return Inertia::render('info-master/Edit', [
            'infoMaster'  => $infoMaster,
            'currentType' => $type,
            // Fetch all unique types for the dropdown
            'masterTypes' => \App\Models\InfoMaster::distinct()->pluck('type'),
            // Fetch parents based on this record's type (e.g., if editing a city, fetch countries)
            'parents'     => $this->getAvailableParents($type),
            'parentLabel' => $this->getParentLabel($type)
        ]);
    }
    public function update(Request $request, InfoMaster $infoMaster)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:info_master,id',
        ]);

        $infoMaster->update($validated);
        return redirect('/info-masters?type=' . $request->type)
            ->with('success', 'Entry updated successfully.');

    }

    public function destroy(InfoMaster $infoMaster)
    {
        $infoMaster->delete();
        return back()->with('success', 'Entry deleted successfully.');
    }

    // Helper to determine parent type logic
    private function getParentType($type)
    {
        return match ($type) {
            'city' => 'country',
            'sub_factory' => 'factory',
            default => null,
        };
    }
    private function getAvailableParents($type)
    {
        return match ($type) {
            'city' => InfoMaster::where('type', 'country')->get(['id', 'name']),
            'sub_factory' => InfoMaster::where('type', 'factory')->get(['id', 'name']),
            'area' => InfoMaster::where('type', 'city')->get(['id', 'name']),
            default => collect([]), // Returns empty collection for types without parents
        };
    }
    private function getParentLabel($type)
    {
        return match ($type) {
            'city' => 'Country',
            'sub_factory' => 'Main Factory',
            'area' => 'City',
            default => 'Category',
        };
    }
}
