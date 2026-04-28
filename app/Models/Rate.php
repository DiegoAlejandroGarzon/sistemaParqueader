<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_type_id', 'price_per_hour', 'fraction_price', 'parking_id'];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function parking()
    {
        return $this->belongsTo(Parking::class);
    }
}
