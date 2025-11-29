<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'is_personwise',
        'total_person_ids',
        'operational_office',
        'operational_office_name',
        'department',
        'department_name',
        'division',
        'division_name',
        'workgroup',
        'workgroup_name',
        'holiday_type',
        'holiday_type_name',
        'holiday_date',
        'date_range',
        'notice_chk',
        'notice_id',
        'moon_setting',
        'remarks',
    ];

    // RELATION: many persons
    public function persons()
    {
        return $this->belongsToMany(Person::class, 'holiday_person', 'holiday_id', 'person_id');
    }
}
