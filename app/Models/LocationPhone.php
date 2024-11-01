<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationPhone extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_id',
        'location_phone',
    ];
}
