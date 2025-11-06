<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $items = $plan->checklistItems;

        return response()->json([
            'data' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);

        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'item' => 'required|string|max:255',
            'is_checked' => 'boolean',
            'order' => 'required|integer|min:0',
        ]);

        $checklistItem = $plan->checklistItems()->create($validated);

        return response()->json([
            'data' => $checklistItem,
            'message' => '持ち物が追加されました。'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = ChecklistItem::findOrFail($id);

        return response()->json([
            'data' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $item = ChecklistItem::findOrFail($id);

        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'item' => 'sometimes|required|string|max:255',
            'is_checked' => 'boolean',
            'order' => 'sometimes|required|integer|min:0',
        ]);

        $item->update($validated);

        return response()->json([
            'data' => $item,
            'message' => '持ち物が更新されました。'
        ]);
    }

    /**
     * Toggle checked status
     */
    public function toggle(string $id): JsonResponse
    {
        $item = ChecklistItem::findOrFail($id);
        $item->update(['is_checked' => !$item->is_checked]);

        return response()->json([
            'data' => $item,
            'message' => 'チェック状態が更新されました。'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $item = ChecklistItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => '持ち物が削除されました。'
        ], 204);
    }
}
