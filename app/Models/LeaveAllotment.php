<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class LeaveAllotment extends Model
{
    use HasFactory;

    protected $table = 'leave_allotments';

    protected $fillable = [
        'year',
        'operational_office',
        'operational_office_name',
        'employee_id',
        'person_id',
        'name',
        'photo',
        'gender',
        'designation',
        'designation_name',
        'division',
        'division_name',
        'department',
        'department_name',
        'annual_allotment',
        'annual_utilized',
        'casual_allotment',
        'casual_utilized',
        'sick_allotment',
        'sick_utilized',
        'maternal_allotment',
        'maternal_utilized',
        'maternal_balance',
        'paternal_allotment',
        'paternal_utilized',
        'earn_allotment',
        'earn_utilized',
        'earn_utilized_encash',
        'this_year_earn_utilized',
        'previous_earn_balance',
        'el_total',
        'total_working_days',
        'previous_earn_utilized',
        'lwp_allotment',
        'lwp_utilized',
        'replacement_leave_allotment',
        'replacement_leave_utilized',
        'replacement_leave_encash',
        'replacement_leave_encash_date',
        'rl_start_date',
        'rl_end_date',
        'replacement_leave_dates',
        'replacement_leave_utl_dates',
        'remarks',
        'reason'
    ];
  /**
     * Link to Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    /**
     * Distribute leave for a given employee and year.
     */
    public static function distributeLeave(Employee $employee, int $allotmentYear)
    {
        // Leave policy
        $annualLeave = 20;
        $casualLeave = 12;
        $sickLeave = 10;

        $joiningDate = $employee->joining_date;
        $joiningYear = date('Y', strtotime($joiningDate));
        $joiningMonth = date('m', strtotime($joiningDate));

        // Prorate if joined in the same year
        if ($joiningYear == $allotmentYear) {
            $remainingMonths = 12 - ($joiningMonth - 1); // include joining month
            $annualLeave = round(($annualLeave / 12) * $remainingMonths);
            $casualLeave = round(($casualLeave / 12) * $remainingMonths);
            $sickLeave = round(($sickLeave / 12) * $remainingMonths);
        }

        // Insert or update leave allotment
        return self::updateOrCreate(
    [
        'employee_id' => $employee->id,
        'year' => $allotmentYear,
    ],
    [
        'annual_allotment' => $annualLeave,
        'casual_allotment' => $casualLeave,
        'sick_allotment' => $sickLeave,
        'name' => $employee->person_name,
        'person_id' => $employee->person_id,
        'designation' => $employee->designation->id ?? null,
        'designation_name' => $employee->designation->name ?? null,
        'department' => $employee->department->id ?? null,
        'department_name' => $employee->department->department_name ?? null,
        'division' => $employee->division->id ?? null,
        'division_name' => $employee->division->division_name ?? null,
    ]
);

    }
}
