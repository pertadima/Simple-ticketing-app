<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_detail_id';
    protected $fillable = ['order_id', 'ticket_id', 'quantity', 'price', 'id_card_number', 'id_card_type', 'seat_id'];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Tickets::class, 'ticket_id');
    }

    public function seat()
    {
        return $this->belongsTo(Seats::class, 'seat_id');
    }
}
