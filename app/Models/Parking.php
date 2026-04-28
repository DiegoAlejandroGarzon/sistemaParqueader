<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    protected $fillable = [
        'name',
        'nit',
        'address',
        'phone',
        'schedule',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function operators()
    {
        return $this->belongsToMany(User::class);
    }

    public function vehicleTypes()
    {
        return $this->hasMany(VehicleType::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
