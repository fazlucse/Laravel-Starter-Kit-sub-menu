<?php
// app/Models/Person.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'name', 'designation', 'phone', 'email', 'country', 'city',
        'address', 'present_address', 'education', 'section',
        'material_status', 'father_name', 'mother_name', 'company',
        'nationality', 'national_id', 'tin', 'avatar', 'status_photo',
    ];

    protected $casts = [
        'material_status' => 'string',
    ];
}