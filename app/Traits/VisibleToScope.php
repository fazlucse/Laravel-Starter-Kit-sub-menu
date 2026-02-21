<?php

namespace App\Traits;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

trait VisibleToScope
{
    /**
     * Common scope for hierarchy-based visibility.
     */
    public function scopeVisibleTo(Builder $query, $user,$module = 'general')
    {
        $fullAccessRoles = ['Developer', 'Super Admin', 'Admin', 'HR Manager','Division Head'];

        // 1. Admins see everything
        if (in_array($user->role, $fullAccessRoles)) {
            return $query;
        }

        // 2. Managers see self and subordinates
        // We look for employees where reporting_manager_id matches the current user
        $supervisedEmployeeIds = Employee::where('reporting_manager_id', $user->employee_id)
            ->pluck('id')
            ->toArray();
        $supervisedEmployeeIds[] = $user->employee_id;
        if($module=='movement'){
            return $query->whereIn($this->getTable() . '.created_by', $user->id);
        }
        return $query->whereIn($this->getTable() . '.employee_id', array_filter($supervisedEmployeeIds));
    }
}
