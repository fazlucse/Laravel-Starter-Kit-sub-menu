<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\MovementRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovementReportController extends Controller
{
    /**
     * Display the filter view with initial data.
     */
    public function index()
    {
        // Fetch unique divisions that have movement records
        $divisions = MovementRegister::whereNotNull('division_name')
            ->distinct()
            ->pluck('division_name');

        return Inertia::render('Reports/MovementReport', [
            'divisions' => $divisions,
            'statuses' => [
                ['id' => 0, 'name' => 'Pending'],
                ['id' => 2, 'name' => 'Approved'],
                ['id' => 22, 'name' => 'Rejected'],
            ]
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'division' => 'nullable|string',
            'employee_name' => 'nullable|string',
            'status' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        try {
            $query = MovementRegister::query();

            // Filters
            $query->when($request->division, fn($q, $v) => $q->where('division_name', $v))
                ->when($request->status !== null, fn($q, $v) => $q->where('status', $v))
                ->when($request->employee_name, fn($q, $v) => $q->where('created_by_name', 'like', "%{$v}%"))
                ->when($request->date_from, fn($q, $v) => $q->whereDate('movement_started_at', '>=', $v))
                ->when($request->date_to, fn($q, $v) => $q->whereDate('movement_started_at', '<=', $v));

            $reportData = $query->orderBy('movement_started_at', 'desc')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'employee' => $row->created_by_name,
                        'division' => $row->division_name,
                        'origin' => $row->origin_location_name,
                        'destination' => $row->destination_location_name,
                        'start_at' => $row->movement_started_at,
                        'end_at' => $row->movement_ended_at,
                        'conveyance' => $row->conveyance_amount,
                        'total_bill' => $row->conveyance_amount + $row->night_amount + $row->holiday_amount + $row->other_amount,
                        'currency' => $row->currency_name,
                        'status' => $this->getStatusLabel($row->status),
                        'purpose' => is_string($row->purpose) ? json_decode($row->purpose) : $row->purpose,
                        'approved_by' => $row->approved_by_name,
                    ];
                });

            return response()->json(['reportData' => $reportData, 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function getStatusLabel($status) {
        return match($status) {
            2 => 'Approved',
            22 => 'Rejected',
            default => 'Pending',
        };
    }
}
