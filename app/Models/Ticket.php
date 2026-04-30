<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate',
        'vehicle_type_id',
        'entry_at',
        'exit_at',
        'total_amount',
        'status',
        'payment_method',
        'user_id',
        'parking_id',
        'notes'
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'exit_at' => 'datetime',
        'status' => TicketStatus::class,
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parking()
    {
        return $this->belongsTo(Parking::class);
    }
}
