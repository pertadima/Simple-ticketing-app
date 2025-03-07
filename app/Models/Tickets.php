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
        return $this->belongsTo(Events::class, 'event_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategories::class, 'category_id');
    }

    public function type()
    {
        return $this->belongsTo(TicketTypes::class, 'type_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'ticket_id');
    }
}
