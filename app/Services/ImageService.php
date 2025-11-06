<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

class ImageService
{
    /**
     * Upload and process an image
     *
     * @param UploadedFile $file
     * @param string $imageableType
     * @param int $imageableId
     * @param string|null $caption
     * @return Image
     */
    public function upload(UploadedFile $file, string $imageableType, int $imageableId, ?string $caption = null): Image
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Define paths
        $path = 'plans/' . $filename;
        $thumbnailPath = 'thumbnails/' . $filename;
        
        // Process and save original image
        $image = InterventionImage::read($file);
        
        // Resize original if too large (max width 1920px)
        if ($image->width() > 1920) {
            $image->scale(width: 1920);
        }
        
        // Save original with quality 90
        $encodedImage = $image->toJpeg(quality: 90);
        Storage::disk('public')->put($path, $encodedImage);
        
        // Create and save thumbnail (max width 800px)
        $thumbnail = InterventionImage::read($file);
        $thumbnail->scale(width: 800);
        $encodedThumbnail = $thumbnail->toJpeg(quality: 85);
        Storage::disk('public')->put($thumbnailPath, $encodedThumbnail);
        
        // Get the last order number for this imageable
        $lastOrder = Image::where('imageable_type', $imageableType)
            ->where('imageable_id', $imageableId)
            ->max('order') ?? -1;
        
        // Create image record
        return Image::create([
            'imageable_type' => $imageableType,
            'imageable_id' => $imageableId,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => '/storage/' . $path,
            'thumbnail_path' => '/storage/' . $thumbnailPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'caption' => $caption,
            'order' => $lastOrder + 1,
        ]);
    }

    /**
     * Delete an image and its files
     *
     * @param Image $image
     * @return bool
     */
    public function delete(Image $image): bool
    {
        // Delete files from storage
        $path = str_replace('/storage/', '', $image->path);
        $thumbnailPath = str_replace('/storage/', '', $image->thumbnail_path);
        
        Storage::disk('public')->delete($path);
        Storage::disk('public')->delete($thumbnailPath);
        
        // Delete database record
        return $image->delete();
    }

    /**
     * Update image caption
     *
     * @param Image $image
     * @param string|null $caption
     * @return Image
     */
    public function updateCaption(Image $image, ?string $caption): Image
    {
        $image->update(['caption' => $caption]);
        return $image->fresh();
    }
}
