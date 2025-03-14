<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'email_veried' => $this->email_verified,
            'orders' => OrdersResource::collection($this->whenLoaded('orders'))
        ];
    }
}
