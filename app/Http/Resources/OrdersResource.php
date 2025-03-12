<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'order_date' => $this->created_at->toIso8601String(),
            'order_detail' => $this->formatTickets(),

        ];
    }

    protected function formatTickets()
    {
        return $this->orderDetails->map(function ($detail) {
            return [
                'event_name' => $detail->ticket->event->name,
                'event_location' => $detail->ticket->event->location,
                'event_date' => Carbon::parse($detail->ticket->event->date)->toIso8601String(),
                'ticket_name' => $detail->ticket->name,
                'ticket_id' => $detail->ticket_id,
                'category' => $detail->ticket->category->name,
                'type' => $detail->ticket->type->name,
                'quantity' => $detail->quantity,
                'unit_price' => (float) $detail->price,
                'subtotal' => (float) ($detail->price * $detail->quantity),
                'requires_id_verification' => (bool) $detail->ticket->requires_id_verification,
                'min_age' => $detail->ticket->min_age
            ];
        });
    }
}
