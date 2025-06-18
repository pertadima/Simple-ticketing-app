<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategories extends Model
{
    protected $primaryKey = 'category_id';

    public function events()
    {
        return $this->belongsToMany(Events::class, 'category_event', 'category_id', 'event_id')
            ->withTimestamps();
    }
}
