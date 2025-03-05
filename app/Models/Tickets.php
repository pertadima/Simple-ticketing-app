<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';
    protected $fillable = [
        'event_id', 'category_id', 'type_id', 
        'price', 'quota', 'sold_count', 
        'min_age', 'requires_id_verification'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function type()
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'ticket_id');
    }
}
