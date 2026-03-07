<?php

namespace App\Constants;

class LeaveConstants
{
    public const REASONS = [
        ['label' => 'Select Reason', 'value' => ''],
        ['label' => 'Sick Leave', 'value' => 'Sick Leave'],
        ['label' => 'Casual Leave', 'value' => 'casual'],
        ['label' => 'Annual Leave', 'value' => 'Annual Leave'],
        ['label' => 'Emergency Leave', 'value' => 'Emergency Leave'],
        ['label' => 'Maternity Leave', 'value' => 'Maternity Leave', 'gender' => 'female'],
        ['label' => 'Paternity Leave', 'value' => 'Paternity Leave', 'gender' => 'male'],
        ['label' => 'LWP (Leave Without Pay)', 'value' => 'LWP (Leave Without Pay)'],
        ['label' => 'Other', 'value' => 'other'],
    ];
}
