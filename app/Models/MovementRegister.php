<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementRegister extends Model
{
    protected $fillable = [
        'origin_location_type', 'destination_location_type',
        'origin_location_name', 'origin_location_id',
        'destination_location_name', 'destination_location_id',
        'purpose', 'transport_mode', 'conveyance_amount',
        'origin_lat', 'origin_lng', 'destination_lat', 'destination_lng',
        'movement_started_at', 'movement_ended_at',
        'created_by', 'created_by_name',
        'division_id', 'division_name',
        'customer_id', 'customer_name',
        'night_allowance', 'holiday_amount', 'other_amount', 'night_amount',
        'currency_id', 'currency_name',
        'base_currency_id', 'base_currency_name',
        'conversion_rate', 'conversion_amount',
        'approved_by', 'approved_by_name', 'approved_at',
        'accounts_confirmed_by', 'accounts_confirm_status',
        'accounts_confirmed_by_name', 'accounts_confirmed_at',
        'budgeted_amount', 'bill_remarks', 'movement_remarks',
        'module_type','visit_type'
    ];

    protected $casts = [
        'movement_started_at' => 'datetime',
        'movement_ended_at' => 'datetime',
        'approved_at' => 'datetime',
        'accounts_confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
