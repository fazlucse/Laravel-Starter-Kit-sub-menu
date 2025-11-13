<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'department_name',
        'department_code',
    ];

    // One department has many employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
