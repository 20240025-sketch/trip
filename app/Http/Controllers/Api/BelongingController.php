<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Belonging;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BelongingController extends Controller
{
    /**
     * Display a listing of the belongings for a plan.
     */
    public function index(Plan $plan): JsonResponse
    {
        $belongings = $plan->belongings()->orderBy('order')->get();

        return response()->json([
            'data' => $belongings
        ]);
    }

    /**
     * Store a newly created belonging.
     */
    public function store(Request $request, Plan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:carry,send',
            'is_checked' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        $validated['plan_id'] = $plan->id;

        $belonging = Belonging::create($validated);

        return response()->json([
            'data' => $belonging,
            'message' => '持ち物が追加されました。'
        ], 201);
    }

    /**
     * Update the specified belonging.
     */
    public function update(Request $request, Belonging $belonging): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:carry,send',
            'is_checked' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        $belonging->update($validated);

        return response()->json([
            'data' => $belonging,
            'message' => '持ち物が更新されました。'
        ]);
    }

    /**
     * Toggle the checked status of a belonging.
     */
    public function toggle(Belonging $belonging): JsonResponse
    {
        $belonging->update([
            'is_checked' => !$belonging->is_checked
        ]);

        return response()->json([
            'data' => $belonging,
            'message' => 'チェック状態を更新しました。'
        ]);
    }

    /**
     * Remove the specified belonging.
     */
    public function destroy(Belonging $belonging): JsonResponse
    {
        $belonging->delete();

        return response()->json([
            'message' => '持ち物が削除されました。'
        ], 204);
    }
}
