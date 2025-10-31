<?php
// app/Http/Controllers/PersonController.php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ActionLog;
use App\Traits\LogsActions;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonController extends Controller
{
    use LogsActions;
    public function index()
    {
        $perPage = request()->integer('per_page', 10); // <-- IMPORTANT
        $people = Person::paginate($perPage);

        // Preserve query string (including per_page)
        $people->appends(request()->query());

        return inertia('person/index', [
            'people' => $people
        ]);
    }

    public function create()
    {
        return Inertia::render('person/create');
    }

    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|unique:people,email',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'present_address' => 'nullable|string',
            'education' => 'nullable|string',
            'section' => 'nullable|string|max:255',
            'material_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|unique:people,national_id',
            'tin' => 'nullable|string|unique:people,tin',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validated = $request->validate($rules);

        // Handle file uploads
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        if ($request->hasFile('status_photo')) {
            $validated['status_photo'] = $request->file('status_photo')->store('status', 'public');
        }

        Person::create($validated);

        return redirect()->route('people.index')
            ->with('success', 'Person created successfully.');
    }

    public function show(Person $person)
    {
        return Inertia::render('People/Show', [
            'person' => $person->makeHidden(['created_at', 'updated_at']),
        ]);
    }

    public function edit(Person $person)
    {
        return Inertia::render('People/Edit', [
            'person' => $person,
        ]);
    }

    public function update(Request $request, Person $person)
    {
        // Validation rules (skip unique on current record)
        $rules = [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:people,email,' . $person->id,
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'present_address' => 'nullable|string',
            'education' => 'nullable|string',
            'section' => 'nullable|string|max:255',
            'material_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|unique:people,national_id,' . $person->id,
            'tin' => 'nullable|string|unique:people,tin,' . $person->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validated = $request->validate($rules);

        // Handle file uploads
        if ($request->hasFile('avatar')) {
            if ($person->avatar) {
                Storage::disk('public')->delete($person->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('status_photo')) {
            if ($person->status_photo) {
                Storage::disk('public')->delete($person->status_photo);
            }
            $validated['status_photo'] = $request->file('status_photo')->store('status', 'public');
        }

        $person->update($validated);

        return redirect()->route('people.show', $person)
            ->with('success', 'Profile updated successfully.');
    }

    public function destroy(Request $request, Person $person)
    {
        if ($person->avatar) {
            Storage::disk('public')->delete($person->avatar);
        }
        if ($person->status_photo) {
            Storage::disk('public')->delete($person->status_photo);
        }
LogsActions::logDelete($person, $request->comments);
        $deleted = $person->delete();

    // Step 2: ONLY log if delete succeeded
    if ($deleted) {
        LogsActions::logDelete($person, $request->comments);
        return back()->with('success', 'Person deleted and logged.');
    }

     return back()->with('error', 'Failed to delete person. It may be in use.');
    }
}