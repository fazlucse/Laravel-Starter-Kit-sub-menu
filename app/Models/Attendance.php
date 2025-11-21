<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'shift',
        'operational_office',
        'operational_office_name',
        'contact_id',
        'employee_id',
        'emp_name',
        'designation',
        'designation_name',
        'department',
        'department_name',
        'division',
        'division_name',
        'serving_division_ids',
        'work_place',
        'work_place_xl',
        'office_in_time',
        'office_out_time',
        'emp_in_time',
        'emp_out_time',
        'work_time',
        'in_time_late',
        'out_time_late',
        'office_lunch_start',
        'office_lunch_end',
        'lunch_start',
        'lunch_end',
        'current_location_xl',
        'current_location',
        'reason',
        'addby',
        'add_time',
        'editby',
        'edit_time',
        'entry_from',
        'out_from',
        'remarks',
        'remarks_out',
        'is_holiday',
        'holiday_name',
        'is_leave_applied',
        'is_leave',
        'leave_count',
        'leave_type',
        'is_offday',
        'business_travel_applied',
        'in_business_travel',
        'manpower_type',
        'manpower_type_name',
        'is_manual',
        'night_allowance',
        'approved_by',
        'approved_by_name',
        'approved_date',
        'approved_remarks',
        'is_email_sent',
        'email_sent_by',
        'email_sent_by_name',
        'email_sent_date',
        'convertion_amount',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'login_ip',
        'connected_mac',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_holiday' => 'boolean',
        'is_leave_applied' => 'boolean',
        'is_leave' => 'boolean',
        'is_offday' => 'boolean',
        'business_travel_applied' => 'boolean',
        'in_business_travel' => 'boolean',
        'in_time_late' => 'integer',
        'out_time_late' => 'integer',
        'night_allowance' => 'double',
        'leave_count' => 'double',
        'approved_by' => 'integer',
        'email_sent_by' => 'integer',
        'add_time' => 'datetime',
        'edit_time' => 'datetime',
        'approved_date' => 'datetime',
        'email_sent_date' => 'datetime',
    ];
}
