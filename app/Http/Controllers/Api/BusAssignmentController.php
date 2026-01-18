<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\BusAssignment;
use Illuminate\Http\Request;

class BusAssignmentController extends Controller
{
    /**
     * 日に紐づくバス座席一覧を取得
     */
    public function index(Day $day)
    {
        $busAssignments = $day->busAssignments()
            ->with('participant')
            ->orderBy('bus_number')
            ->orderBy('row_number')
            ->orderBy('side')
            ->get();

        return response()->json($busAssignments);
    }

    /**
     * バス座席を新規作成
     */
    public function store(Request $request, Day $day)
    {
        $validated = $request->validate([
            'participant_id' => 'nullable|exists:participants,id',
            'bus_number' => 'nullable|string|max:50',
            'row_number' => 'nullable|string|max:50',
            'side' => 'nullable|in:left,right',
            'notes' => 'nullable|string',
        ]);

        $validated['plan_id'] = $day->plan_id;
        $busAssignment = $day->busAssignments()->create($validated);
        $busAssignment->load('participant');

        return response()->json($busAssignment, 201);
    }

    /**
     * バス座席を更新
     */
    public function update(Request $request, BusAssignment $busAssignment)
    {
        $validated = $request->validate([
            'participant_id' => 'nullable|exists:participants,id',
            'bus_number' => 'nullable|string|max:50',
            'row_number' => 'nullable|string|max:50',
            'side' => 'nullable|in:left,right',
            'notes' => 'nullable|string',
        ]);

        $busAssignment->update($validated);
        $busAssignment->load('participant');

        return response()->json($busAssignment);
    }

    /**
     * バス座席を削除
     */
    public function destroy(BusAssignment $busAssignment)
    {
        $busAssignment->delete();

        return response()->json(null, 204);
    }
}
