<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seats extends Model
{
    protected $primaryKey = 'seat_id';
    protected $fillable = [
        'event_id', 'type_id', 'seat_number', 'is_booked'
    ];

    public function event()
    {
        return $this->belongsTo(Events::class, 'event_id');
    }

    public function type()
    {
        return $this->belongsTo(TicketTypes::class, 'type_id');
    }
}
