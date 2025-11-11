<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';

    protected $fillable = [
        'division_name',
        'division_code',
    ];

    // One division has many employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'division_id');
    }
}
