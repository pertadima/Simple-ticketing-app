<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $primaryKey = 'event_id';
    protected $fillable = ['name', 'date', 'location', 'description'];

    public function tickets()
    {
        return $this->hasMany(Tickets::class, 'event_id');
    }

    public function images()
    {
        return $this->hasMany(EventImages::class, 'event_id');
    }

    public function categories()
    {
        return $this->belongsToMany(EventCategories::class, 'category_event', 'event_id', 'category_id')
          ->withTimestamps();
    }
}
