<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class ApiUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'api_users';
    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'email', 
        'password_hash', 
        'full_name',
        'name',
        'password',
        'email_verified'
    ];

    protected $hidden = [
        'password_hash',
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_hash' => 'hashed',
        'email_verified' => 'boolean',
    ];

    // Tell Laravel which field to use for authentication
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Override the password field name for authentication
    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    // Map password attribute to password_hash
    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = Hash::make($value);
    }

    // Map name attribute to full_name
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
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
}
