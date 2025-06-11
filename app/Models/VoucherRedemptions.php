<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherRedemptions extends Model
{
    protected $fillable = ['voucher_id', 'user_id', 'order_id'];
}
