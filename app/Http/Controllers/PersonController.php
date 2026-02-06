<?php
// app/Http/Controllers/PersonController.php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ActionLog;
use App\Models\InfoMaster;
use App\Traits\LogsActions;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PersonController extends Controller
{
    use LogsActions;
    public function index(Request $request)
    {
    $perPage = (int) $request->query('per_page', 15);
    $query = Person::query();
    $search = $request->input('search');
    $query = Person::query();
    $filters = $request->only(['name', 'email', 'phone', 'designation', 'city']);
    foreach ($filters as $field => $value) {
        if (!empty($value)) {
            $query->where($field, 'like', "%{$value}%");
        }
    }
    $people = $query->orderByDesc('id')
                    ->paginate($perPage)
                    ->appends($request->query());

    return inertia('person/index', [
        'people' => $people,
        'perPage' => (int) $request->query('per_page', 15),
        'defaultPerPage' => 15,
        'search'=>$search,
          'filters' => $filters,
            'flash' => [
                'success' => session('success'),
            ],
    ])->with('success', 'Person created.');
    }

    public function create()
    {
        return Inertia::render('person/create',[
        'countries' => InfoMaster::getCountries(),
        'cities' => InfoMaster::getCities(),
        ]);
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
            'dob' => 'nullable|date',
            'religion' => 'nullable|string|max:255',
            'gender' =>  'nullable|string|max:255'
        ];

        $validated = $request->validate($rules);

        // Handle file uploads
        if ($request->hasFile('avatar')) {
           $statusDir = public_path('images/user_photo');
            if (!File::exists($statusDir)) {
                File::makeDirectory($statusDir, 0755, true);
            }
            $avatarFile = $request->file('avatar');
            $statusName = time() . '_' . $avatarFile->getClientOriginalName();
            $avatarFile->move($statusDir, $statusName);
            $validated['photo'] = 'images/user_photo/' . $statusName;
        }

        $person = Person::create($validated);
        $lastId = $person->id;
        LogsActions::logCreate($person, 'Person created successfully.');
         session()->flash('success', 'Person created successfully.');
     return redirect()->route('people.index');
    }

    public function show(Person $person)
    {
        return Inertia::render('person/show', [
            'person' => $person->makeHidden(['created_at', 'updated_at']),
        ]);
    }

    public function edit(Person $person)
    {
        return Inertia::render('person/create', [
            'person' => $person,
            'countries' => InfoMaster::getCountries(),
            'cities' => InfoMaster::getCities(),
        ]);
    }

    public function update(Request $request, Person $person)
    {
        $rules = [
        'name' => 'required|string|max:255',
        'designation' => 'nullable|string|max:255',
        'phone' => 'required|string|max:20',
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
        'dob' => 'nullable|date',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'religion' => 'nullable|string|max:255',
        'gender' =>  'nullable|string|max:255'
    ];

    $validated = $request->validate($rules);

    // Handle avatar
    if ($request->hasFile('avatar')) {
        // Delete old avatar
        if ($person->photo && File::exists(public_path($person->photo))) {
            File::delete(public_path($person->photo));
        }
        $avatarFile = $request->file('avatar');
        $avatarName = time() . '_' . $avatarFile->getClientOriginalName();
        $avatarFile->move(public_path('images/user_photo'), $avatarName);
        $validated['photo'] = 'images/user_photo/' . $avatarName;
    } else {
        $validated['photo'] = $person->avatar; // keep old avatar
    }

    $person->update($validated);

    return redirect()->route('people.index')->with('success', 'Profile updated successfully.');
    }

    public function destroy(Request $request, Person $person)
    {
         if ($person->photo && File::exists(public_path($person->photo))) {
            File::delete(public_path($person->photo));
        }
        LogsActions::logDelete($person, $request->comments);
        $deleted = $person->delete(); // or forceDelete() if soft-deleted
       if ($deleted) {
        return redirect()
            ->route('people.index')
            ->with('success', 'Person deleted.');
    }
    return back()->with('success', 'Deleted');

    //  return back()->with('error', 'Failed to delete person. It may be in use.');
    }
    public function search(Request $request)
{
    $query = $request->input('q', '');

    $persons = Person::query()
        ->when($query, fn($q) => $q->where('name', 'like', "%{$query}%"))
        ->select('id', 'name','email','phone')
        ->limit(10)
        ->get();

    return response()->json(['person' => $persons]);
}
}
