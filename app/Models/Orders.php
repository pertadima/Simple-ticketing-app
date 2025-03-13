<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';
    
    protected $fillable = [
        'user_id', 'total_amount', 'status',
        'id_card_type', 'id_card_number', 'email_verified'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'order_id');
    }
}
