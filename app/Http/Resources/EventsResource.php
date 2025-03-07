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
            'id' => $this->event_id,
            'name' => $this->name,
            'date' => $this->date,
            'location' => $this->location,
            'description' => $this->description,
            'images' => $this->images->pluck('image_url'),
            'tickets' => TicketsResource::collection($this->whenLoaded('tickets'))
        ];
    }
}
