<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vouchers extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'discount', 'discount_type', 
        'event_id', 'valid_until', 'usage_limit', 'used_count'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Events::class);
    }

    public function isValid(): bool
    {
        return $this->valid_until > now() && 
               $this->used_count < $this->usage_limit;
    }
}
