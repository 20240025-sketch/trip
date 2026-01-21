<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusAssignment;
use App\Models\Participant;
use App\Models\Plan;
use App\Models\RoomAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantAssignmentController extends Controller
{
    /**
     * プランの参加者とその割り当て一覧を取得
     */
    public function index(string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $participants = $plan->participants()
            ->with(['busAssignments', 'roomAssignments'])
            ->get();

        return response()->json([
            'data' => $participants
        ]);
    }

    /**
     * 参加者とバス・部屋割を一括作成・更新
     */
    public function bulkStore(Request $request, string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);

        $validated = $request->validate([
            'participants' => 'required|array',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.email' => 'nullable|email|max:255',
            'participants.*.class_name' => 'nullable|string|max:255',
            'participants.*.contact' => 'nullable|string|max:255',
            'participants.*.bus_assignments' => 'nullable|array',
            'participants.*.bus_assignments.*.day_id' => 'required|exists:days,id',
            'participants.*.bus_assignments.*.bus_number' => 'required|string|max:50',
            'participants.*.bus_assignments.*.row_number' => 'required|string|max:50',
            'participants.*.room_assignments' => 'nullable|array',
            'participants.*.room_assignments.*.day_id' => 'required|exists:days,id',
            'participants.*.room_assignments.*.room_number' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $createdParticipants = [];

            foreach ($validated['participants'] as $participantData) {
                // 参加者を作成
                $participant = $plan->participants()->create([
                    'name' => $participantData['name'],
                    'email' => $participantData['email'] ?? null,
                    'class_name' => $participantData['class_name'] ?? null,
                    'contact' => $participantData['contact'] ?? null,
                ]);

                // バス座席を作成
                if (!empty($participantData['bus_assignments'])) {
                    foreach ($participantData['bus_assignments'] as $busData) {
                        BusAssignment::create([
                            'plan_id' => $plan->id,
                            'day_id' => $busData['day_id'],
                            'participant_id' => $participant->id,
                            'bus_number' => $busData['bus_number'],
                            'row_number' => $busData['row_number'],
                        ]);
                    }
                }

                // 部屋割を作成
                if (!empty($participantData['room_assignments'])) {
                    foreach ($participantData['room_assignments'] as $roomData) {
                        RoomAssignment::create([
                            'plan_id' => $plan->id,
                            'day_id' => $roomData['day_id'],
                            'participant_id' => $participant->id,
                            'room_number' => $roomData['room_number'],
                        ]);
                    }
                }

                $createdParticipants[] = $participant->load(['busAssignments', 'roomAssignments']);
            }

            DB::commit();

            return response()->json([
                'data' => $createdParticipants,
                'message' => '参加者とその割り当てが追加されました。'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 参加者とその割り当てを更新
     */
    public function update(Request $request, string $participantId): JsonResponse
    {
        $participant = Participant::findOrFail($participantId);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'class_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'bus_assignments' => 'nullable|array',
            'bus_assignments.*.id' => 'nullable|exists:bus_assignments,id',
            'bus_assignments.*.day_id' => 'required|exists:days,id',
            'bus_assignments.*.bus_number' => 'required|string|max:50',
            'bus_assignments.*.row_number' => 'required|string|max:50',
            'room_assignments' => 'nullable|array',
            'room_assignments.*.id' => 'nullable|exists:room_assignments,id',
            'room_assignments.*.day_id' => 'required|exists:days,id',
            'room_assignments.*.room_number' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // 参加者を更新
            $participant->update([
                'name' => $validated['name'] ?? $participant->name,
                'email' => $validated['email'] ?? $participant->email,
                'class_name' => $validated['class_name'] ?? $participant->class_name,
                'contact' => $validated['contact'] ?? $participant->contact,
            ]);

            // バス座席を更新
            if (isset($validated['bus_assignments'])) {
                // 既存のバス座席をすべて削除
                BusAssignment::where('participant_id', $participant->id)->delete();

                // 新しいバス座席を作成
                foreach ($validated['bus_assignments'] as $busData) {
                    BusAssignment::create([
                        'plan_id' => $participant->plan_id,
                        'day_id' => $busData['day_id'],
                        'participant_id' => $participant->id,
                        'bus_number' => $busData['bus_number'],
                        'row_number' => $busData['row_number'],
                    ]);
                }
            }

            // 部屋割を更新
            if (isset($validated['room_assignments'])) {
                // 既存の部屋割をすべて削除
                RoomAssignment::where('participant_id', $participant->id)->delete();

                // 新しい部屋割を作成
                foreach ($validated['room_assignments'] as $roomData) {
                    RoomAssignment::create([
                        'plan_id' => $participant->plan_id,
                        'day_id' => $roomData['day_id'],
                        'participant_id' => $participant->id,
                        'room_number' => $roomData['room_number'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'data' => $participant->load(['busAssignments', 'roomAssignments']),
                'message' => '参加者とその割り当てが更新されました。'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }
}
