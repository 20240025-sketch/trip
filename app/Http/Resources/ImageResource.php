<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
            'imageable_type' => $this->imageable_type,
            'imageable_id' => $this->imageable_id,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'path' => $this->path,
            'thumbnail_path' => $this->thumbnail_path,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'caption' => $this->caption,
            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
