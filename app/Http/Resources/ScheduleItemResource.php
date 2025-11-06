<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day_id' => $this->day_id,
            'time' => $this->time?->format('H:i'),
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'transport_type' => $this->transport_type,
            'transport_from' => $this->transport_from,
            'transport_to' => $this->transport_to,
            'transport_duration' => $this->transport_duration,
            'transport_cost' => $this->transport_cost,
            'order' => $this->order,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
