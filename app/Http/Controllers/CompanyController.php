<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Traits\LogsActions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Companies/Index', [
            'companies' => $companies
        ]);
    }

    public function create()
    {
        return Inertia::render('Companies/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'status'                => 'required|in:Active,Inactive',
            'type'                  => 'required|string|max:150',
            'company_code'          => 'nullable|string|max:50',
            'address'               => 'nullable|string',
            'city'                  => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'country'               => 'nullable|string|max:100',
            'email'                 => 'nullable|email|max:150',
            'phone'                 => 'nullable|string|max:50',
            'postal_code'           => 'nullable|string|max:20',
            'address_line1'         => 'nullable|string|max:255',
            'address_line2'         => 'nullable|string|max:255',
            'website'               => 'nullable|string|max:150',
            'ownership_type'        => 'nullable|string|max:100',
            'tax_identification_no' => 'nullable|string|max:100',
            'registration_no'       => 'nullable|string|max:100',
            'logo'                  => 'nullable|image|max:2048',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('logo')) {
                $path = public_path('uploads/logos');
                if (!File::isDirectory($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }

                $file = $request->file('logo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $validated['logo_path'] = 'uploads/logos/' . $fileName;
            }

            unset($validated['logo']);

            $company = Company::create(array_merge($validated, [
                'created_by' => Auth::id()
            ]));

            // Log the creation
            LogsActions::logCreate($company, 'Company created successfully.');

            return redirect()->route('companies.index')
                ->with('success', 'Company created successfully.');
        });
    }

    public function show(Company $company)
    {
        return Inertia::render('Companies/Show', ['company' => $company]);
    }

    public function edit(Company $company)
    {
        return Inertia::render('Companies/Form', ['company' => $company]);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'status'                => 'required|in:Active,Inactive',
            'logo'                  => 'nullable|image|max:2048',
        ]);

        return DB::transaction(function () use ($request, $validated, $company) {
            if ($request->hasFile('logo')) {
                $path = public_path('uploads/logos');
                if (!File::isDirectory($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }

                if ($company->logo_path && File::exists(public_path($company->logo_path))) {
                    File::delete(public_path($company->logo_path));
                }

                $file = $request->file('logo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $validated['logo_path'] = 'uploads/logos/' . $fileName;
            }

            unset($validated['logo']);

            $company->update(array_merge($request->all(), $validated, [
                'updated_by' => Auth::id()
            ]));

            // Log the update
            LogsActions::logUpdate($company, 'Company updated successfully.');

            return redirect()->route('companies.index')
                ->with('success', 'Company updated successfully.');
        });
    }

    public function destroy(Company $company)
    {
        return DB::transaction(function () use ($company) {
            if ($company->logo_path && File::exists(public_path($company->logo_path))) {
                File::delete(public_path($company->logo_path));
            }

            // Log before deletion to ensure we have the record data for the log
            LogsActions::logDelete($company, 'Company deleted successfully.');

            $company->delete();

            return redirect()->route('companies.index')
                ->with('success', 'Company deleted successfully.');
        });
    }
}
