<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $primaryKey = 'user_id';
    protected $fillable = [
        'email', 
        'password_hash', 
        'full_name'
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // Tell Laravel which field to use for authentication
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Relationships
    public function orders()
    {
        return $this->hasMany(Orders::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasManyThrough(
            Tickets::class,
            OrderDetails::class,
            'order_id', // Foreign key on order_details table
            'ticket_id', // Foreign key on tickets table
            'user_id',   // Local key on users table
            'id'         // Local key on orders table
        );
    }

    public function age()
    {
        return $this->birth_date ? now()->diffInYears($this->birth_date) : null;
    }

    public function getIdVerifiedAttribute()
    {
        return (bool) $this->attributes['id_verified'];
    }
}