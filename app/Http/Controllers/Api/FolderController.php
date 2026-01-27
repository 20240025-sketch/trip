<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     * Display a listing of folders
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }
        
        // 自分のフォルダと、is_private=false の他人のフォルダを取得
        $folders = Folder::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('is_private', false);
            })
            ->with(['plans' => function($query) use ($user) {
                // フォルダ内のプランも権限チェック
                $query->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('is_public', true);
                });
            }])
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($folders->map(function($folder) use ($user) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'is_private' => $folder->is_private,
                'order' => $folder->order,
                'plan_count' => $folder->plans->count(),
                'is_owner' => $folder->user_id === $user->id,
                'created_at' => $folder->created_at->toISOString(),
            ];
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'boolean',
        ]);
        
        $folder = Folder::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'is_private' => $validated['is_private'] ?? false,
            'order' => Folder::where('user_id', $user->id)->max('order') + 1,
        ]);
        
        return response()->json([
            'id' => $folder->id,
            'name' => $folder->name,
            'is_private' => $folder->is_private,
            'order' => $folder->order,
            'plan_count' => 0,
            'is_owner' => true,
            'created_at' => $folder->created_at->toISOString(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        $folder = Folder::findOrFail($id);
        
        // プライベートフォルダの場合、所有者のみアクセス可能
        if ($folder->is_private && (!$user || $folder->user_id !== $user->id)) {
            return response()->json([
                'message' => 'このフォルダを閲覧する権限がありません。'
            ], 403);
        }
        
        $folder->load(['plans' => function($query) use ($user) {
            if ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('is_public', true);
                });
            } else {
                $query->where('is_public', true);
            }
        }]);
        
        return response()->json([
            'id' => $folder->id,
            'name' => $folder->name,
            'is_private' => $folder->is_private,
            'order' => $folder->order,
            'plan_count' => $folder->plans->count(),
            'is_owner' => $user && $folder->user_id === $user->id,
            'plans' => $folder->plans,
            'created_at' => $folder->created_at->toISOString(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }
        
        $folder = Folder::findOrFail($id);
        
        if ($folder->user_id !== $user->id) {
            return response()->json([
                'message' => 'このフォルダを編集する権限がありません。'
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_private' => 'sometimes|boolean',
        ]);
        
        $folder->update($validated);
        
        return response()->json([
            'id' => $folder->id,
            'name' => $folder->name,
            'is_private' => $folder->is_private,
            'order' => $folder->order,
            'plan_count' => $folder->plans->count(),
            'is_owner' => true,
            'created_at' => $folder->created_at->toISOString(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }
        
        $folder = Folder::findOrFail($id);
        
        if ($folder->user_id !== $user->id) {
            return response()->json([
                'message' => 'このフォルダを削除する権限がありません。'
            ], 403);
        }
        
        $folder->delete();
        
        return response()->json([
            'message' => 'フォルダを削除しました。'
        ]);
    }

    /**
     * Add a plan to a folder
     */
    public function addPlan(Request $request, string $id): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }
        
        $folder = Folder::findOrFail($id);
        
        if ($folder->user_id !== $user->id) {
            return response()->json([
                'message' => 'このフォルダを編集する権限がありません。'
            ], 403);
        }
        
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);
        
        $folder->plans()->syncWithoutDetaching([$validated['plan_id']]);
        
        return response()->json([
            'message' => 'プランをフォルダに追加しました。'
        ]);
    }

    /**
     * Remove a plan from a folder
     */
    public function removePlan(string $id, string $planId): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }
        
        $folder = Folder::findOrFail($id);
        
        if ($folder->user_id !== $user->id) {
            return response()->json([
                'message' => 'このフォルダを編集する権限がありません。'
            ], 403);
        }
        
        $folder->plans()->detach($planId);
        
        return response()->json([
            'message' => 'フォルダからプランを削除しました。'
        ]);
    }
}
