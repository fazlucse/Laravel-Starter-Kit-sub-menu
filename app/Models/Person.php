<?php
// app/Models/Person.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Person extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'designation', 'phone', 'email', 'country', 'city',
        'address', 'present_address', 'education', 'section',
        'material_status', 'father_name', 'mother_name', 'company',
        'nationality', 'national_id', 'tin', 'avatar', 'dob','gender','religion'
    ];

    protected $casts = [
        'material_status' => 'string',
    ];
}