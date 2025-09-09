<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicketType extends Model
{
    use HasFactory;
    
    protected $table = 'event_ticket_types';
    protected $fillable = ['event_id', 'type_id', 'has_seat_number'];

    public function event()
    {
        return $this->belongsTo(Events::class, 'event_id');
    }

    public function type()
    {
        return $this->belongsTo(TicketTypes::class, 'type_id');
    }
}
