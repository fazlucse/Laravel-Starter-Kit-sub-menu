<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoMaster extends Model
{
    use HasFactory;

    protected $table = 'info_master';

    protected $fillable = [
        'type',
        'name',
        'code',
        'description',
        'parent_id',
        'created_by',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(InfoMaster::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(InfoMaster::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
        public static function getCountries()
    {
        return self::where('type', 'country')->select('id', 'name')->get();
    }

    public static function getCities()
    {
        return self::where('type', 'city')->select('id', 'name', 'parent_id')->get();
    }

    // Optional: Get cities by country
    public static function getCitiesByCountry($countryId)
    {
        return self::where('type', 'city')
            ->where('parent_id', $countryId)
            ->select('id', 'name')
            ->get();
    }
}
