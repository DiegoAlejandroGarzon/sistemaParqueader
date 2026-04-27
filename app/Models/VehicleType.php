<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'capacity'];

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
