<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'cover_image' => $this->cover_image,
            'is_public' => $this->is_public,
            'slug' => $this->slug,
            'memo' => $this->memo,
            'days' => DayResource::collection($this->whenLoaded('days')),
            'participants' => $this->whenLoaded('participants'),
            'checklist_items' => $this->whenLoaded('checklistItems'),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Add permission flags for frontend
            'can_view' => $this->canView($user),
            'can_edit' => $this->canEdit($user),
            'can_delete' => $this->canDelete($user),
        ];
    }
}
