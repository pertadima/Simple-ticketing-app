<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ticket_id,
            'category' => $this->category->name,
            'type' => $this->type->name,
            'price' => $this->price,
            'quota' => $this->quota,
            'min_age' => $this->min_age,
            'requires_id_verification' => $this->requires_id_verification
        ];
    }
}
