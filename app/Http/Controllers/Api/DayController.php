<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DayResource;
use App\Models\Day;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $days = $plan->days()->with('scheduleItems.images')->get();

        return response()->json([
            'data' => DayResource::collection($days)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);

        $validated = $request->validate([
            'date' => 'required|date',
            'day_number' => 'required|integer',
            'title' => 'nullable|string|max:255',
        ]);

        $day = $plan->days()->create($validated);

        return response()->json([
            'data' => new DayResource($day),
            'message' => '日程が作成されました。'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $day = Day::with('scheduleItems.images')->findOrFail($id);

        return response()->json([
            'data' => new DayResource($day)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $day = Day::findOrFail($id);

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'day_number' => 'sometimes|integer',
            'title' => 'nullable|string|max:255',
        ]);

        $day->update($validated);

        return response()->json([
            'data' => new DayResource($day),
            'message' => '日程が更新されました。'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $day = Day::findOrFail($id);
        $day->delete();

        return response()->json([
            'message' => '日程が削除されました。'
        ], 204);
    }
}
