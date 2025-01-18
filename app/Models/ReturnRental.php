<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRental extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'return_date',
        'total_amount',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
