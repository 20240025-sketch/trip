<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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
        // Log upload details
        Log::info('Image upload started', [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
        ]);
        
        // Generate unique filename preserving original extension
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Define storage paths  
        $path = 'plans/' . $filename;
        $thumbnailPath = 'thumbnails/' . $filename;
        
        // Ensure directories exist
        Storage::disk('public')->makeDirectory('plans');
        Storage::disk('public')->makeDirectory('thumbnails');
        
        // Store original image directly without any processing
        $storedPath = $file->storeAs('plans', $filename, 'public');
        
        // Copy to thumbnails as well (can add actual thumbnail processing later if needed)
        $file->storeAs('thumbnails', $filename, 'public');
        
        // Verify file was saved correctly
        $fullPath = Storage::disk('public')->path($path);
        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
        
        Log::info('Image upload completed', [
            'filename' => $filename,
            'stored_path' => $storedPath,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'saved_size' => $fileSize,
            'original_size' => $file->getSize(),
        ]);
        
        // Get the last order number for this imageable
        $lastOrder = Image::where('imageable_type', $imageableType)
            ->where('imageable_id', $imageableId)
            ->max('order') ?? -1;
        
        // Create image record with /storage/ prefix for public access
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
