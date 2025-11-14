<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlanAttachmentController extends Controller
{
    /**
     * Display a listing of attachments for a plan
     */
    public function index(Request $request, string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        
        // Check if user can view this plan (allow null user for public plans)
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'この旅行計画を閲覧する権限がありません。'
            ], 403);
        }

        $attachments = $plan->attachments()->get()->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'file_size' => $attachment->file_size,
                'formatted_size' => $attachment->getFormattedSize(),
                'url' => $attachment->getUrl(),
                'is_image' => $attachment->isImage(),
                'is_pdf' => $attachment->isPdf(),
                'extension' => $attachment->getExtension(),
                'created_at' => $attachment->created_at->toISOString(),
            ];
        });

        return response()->json($attachments);
    }

    /**
     * Store a newly created attachment
     */
    public function store(Request $request, string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        
        // Check if user can edit this plan
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'この旅行計画を編集する権限がありません。'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 最大10MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('attachments', $storedName, 'public');

        $attachment = PlanAttachment::create([
            'plan_id' => $plan->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'order' => $plan->attachments()->count(),
        ]);

        return response()->json([
            'id' => $attachment->id,
            'original_name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'file_size' => $attachment->file_size,
            'formatted_size' => $attachment->getFormattedSize(),
            'url' => $attachment->getUrl(),
            'is_image' => $attachment->isImage(),
            'is_pdf' => $attachment->isPdf(),
            'extension' => $attachment->getExtension(),
            'created_at' => $attachment->created_at->toISOString(),
        ], 201);
    }

    /**
     * Remove the specified attachment
     */
    public function destroy(Request $request, string $planId, string $id): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        
        // Check if user can edit this plan
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'この旅行計画を編集する権限がありません。'
            ], 403);
        }

        $attachment = PlanAttachment::where('plan_id', $plan->id)
            ->where('id', $id)
            ->firstOrFail();

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json([
            'message' => 'ファイルを削除しました。'
        ], 200);
    }

    /**
     * Download the specified attachment
     */
    public function download(Request $request, string $planId, string $id)
    {
        $plan = Plan::findOrFail($planId);
        
        // Check if user can view this plan (allow null user for public plans)
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'この旅行計画を閲覧する権限がありません。'
            ], 403);
        }

        $attachment = PlanAttachment::where('plan_id', $plan->id)
            ->where('id', $id)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json([
                'message' => 'ファイルが見つかりません。'
            ], 404);
        }

        $filePath = Storage::disk('public')->path($attachment->file_path);

        return response()->download($filePath, $attachment->original_name);
    }
}
