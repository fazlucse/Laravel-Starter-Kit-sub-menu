<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
