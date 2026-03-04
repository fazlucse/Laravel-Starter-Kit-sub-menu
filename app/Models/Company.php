<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'type',
        'company_code',
        'address',
        'city',
        'state',
        'country',
        'email',
        'phone',
        'country',
        'state',
        'postal_code',
        'address_line1',
        'address_line2',
        'website',
        'logo',
        'ownership_type',
        'tax_identification_no',
        'registration_no',
        'status',
        'logo_path'
    ];

    // One company has many employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'company_id');
    }
}
