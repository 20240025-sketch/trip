<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleItemRequest;
use App\Http\Resources\ScheduleItemResource;
use App\Models\Day;
use App\Models\ScheduleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $dayId): JsonResponse
    {
        $day = Day::findOrFail($dayId);
        $items = $day->scheduleItems()->with('images')->get();

        return response()->json([
            'data' => ScheduleItemResource::collection($items)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleItemRequest $request, string $dayId): JsonResponse
    {
        $day = Day::findOrFail($dayId);
        
        $data = $request->validated();
        $data['day_id'] = $day->id;
        
        $item = ScheduleItem::create($data);

        return response()->json([
            'data' => new ScheduleItemResource($item),
            'message' => 'スケジュールが作成されました。'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = ScheduleItem::with('images')->findOrFail($id);

        return response()->json([
            'data' => new ScheduleItemResource($item)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $item = ScheduleItem::findOrFail($id);

        $validated = $request->validate([
            'time' => 'nullable|date_format:H:i',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'transport_type' => 'nullable|string|in:train,bus,plane,car,walk,other',
            'transport_from' => 'nullable|string|max:255',
            'transport_to' => 'nullable|string|max:255',
            'transport_duration' => 'nullable|integer|min:0',
            'transport_cost' => 'nullable|integer|min:0',
            'order' => 'sometimes|required|integer|min:0',
        ]);

        $item->update($validated);

        return response()->json([
            'data' => new ScheduleItemResource($item),
            'message' => 'スケジュールが更新されました。'
        ]);
    }

    /**
     * Reorder schedule items
     */
    public function reorder(Request $request, string $id): JsonResponse
    {
        $item = ScheduleItem::findOrFail($id);

        $validated = $request->validate([
            'order' => 'required|integer|min:0',
        ]);

        $item->update(['order' => $validated['order']]);

        return response()->json([
            'data' => new ScheduleItemResource($item),
            'message' => '順序が更新されました。'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $item = ScheduleItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'スケジュールが削除されました。'
        ], 204);
    }
}
