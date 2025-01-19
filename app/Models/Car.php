<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'license_plate',
        'rental_price_per_day',
        'is_available',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
