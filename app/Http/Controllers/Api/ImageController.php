<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Upload a new image
     */
    public function upload(UploadImageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $image = $this->imageService->upload(
            $request->file('image'),
            $validated['imageable_type'],
            $validated['imageable_id'],
            $validated['caption'] ?? null
        );

        return response()->json([
            'data' => new ImageResource($image),
            'message' => '画像がアップロードされました。'
        ], 201);
    }

    /**
     * Update image caption
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $image = Image::findOrFail($id);

        $validated = $request->validate([
            'caption' => 'nullable|string|max:500',
            'order' => 'sometimes|integer|min:0',
        ]);

        $image->update($validated);

        return response()->json([
            'data' => new ImageResource($image),
            'message' => '画像が更新されました。'
        ]);
    }

    /**
     * Delete an image
     */
    public function destroy(string $id): JsonResponse
    {
        $image = Image::findOrFail($id);
        $this->imageService->delete($image);

        return response()->json([
            'message' => '画像が削除されました。'
        ], 204);
    }
}
