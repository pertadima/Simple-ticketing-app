<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketType extends Model
{
    protected $table = 'event_ticket_types';
    protected $fillable = ['event_id', 'type_id', 'has_seat_number'];
}
