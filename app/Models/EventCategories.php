<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategories extends Model
{
    use HasFactory;
    
    protected $table = 'event_categories';
    protected $primaryKey = 'category_id';

    public function events()
    {
        return $this->belongsToMany(Events::class, 'category_event', 'category_id', 'event_id')
            ->withTimestamps();
    }
}
