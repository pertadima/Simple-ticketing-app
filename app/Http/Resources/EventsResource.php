<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'event_id' => $this->event_id,
            'name' => $this->name,
            'date' => $this->date,
            'time' => $this->date ? $this->date->format('H:i:s') : null,
            'location' => $this->location,
            'description' => $this->description,
            'images' => $this->images->pluck('image_url'),
            'categories' => $this->categories->map(function ($category) {
                return [
                    'id' => $category->category_id,
                    'name' => $category->name, // adjust field names based on your EventCategory model
                ];
            })
        ];
    }
}
