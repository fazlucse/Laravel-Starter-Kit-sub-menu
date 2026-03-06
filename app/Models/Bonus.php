<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_id',
        'bonus_date',
        'bonus_type',
        'amount',
        'payroll_month',
        'remarks',
        'is_paid',
        'processed_at'
    ];

    protected $casts = [
        'bonus_date'   => 'date',
        'is_paid'      => 'boolean',
        'processed_at' => 'datetime',
        'amount'       => 'decimal:2'
    ];

    public function employee() { return $this->belongsTo(Employee::class, 'employee_id'); }
    public function payroll()  { return $this->belongsTo(Payroll::class, 'payroll_id'); }
}
