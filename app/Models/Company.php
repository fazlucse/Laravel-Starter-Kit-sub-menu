<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'company_name',
        'company_code',
        'address',
        'city',
        'state',
        'country',
        'email',
        'phone',
    ];

    // One company has many employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'company_id');
    }
}
