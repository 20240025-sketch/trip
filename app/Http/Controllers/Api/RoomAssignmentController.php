<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;

class RoomAssignmentController extends Controller
{
    /**
     * 日に紐づく部屋割一覧を取得
     */
    public function index(Day $day)
    {
        $roomAssignments = $day->roomAssignments()
            ->with('participant')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        return response()->json($roomAssignments);
    }

    /**
     * 部屋割を新規作成
     */
    public function store(Request $request, Day $day)
    {
        $validated = $request->validate([
            'participant_id' => 'nullable|exists:participants,id',
            'floor' => 'nullable|string|max:50',
            'room_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['plan_id'] = $day->plan_id;
        $roomAssignment = $day->roomAssignments()->create($validated);
        $roomAssignment->load('participant');

        return response()->json($roomAssignment, 201);
    }

    /**
     * 部屋割を更新
     */
    public function update(Request $request, RoomAssignment $roomAssignment)
    {
        $validated = $request->validate([
            'participant_id' => 'nullable|exists:participants,id',
            'floor' => 'nullable|string|max:50',
            'room_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $roomAssignment->update($validated);
        $roomAssignment->load('participant');

        return response()->json($roomAssignment);
    }

    /**
     * 部屋割を削除
     */
    public function destroy(RoomAssignment $roomAssignment)
    {
        $roomAssignment->delete();

        return response()->json(null, 204);
    }
}
