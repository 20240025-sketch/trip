<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $participants = $plan->participants;

        return response()->json([
            'data' => $participants
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:255',
        ]);

        $participant = $plan->participants()->create($validated);

        return response()->json([
            'data' => $participant,
            'message' => '参加者が追加されました。'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $participant = Participant::findOrFail($id);

        return response()->json([
            'data' => $participant
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $participant = Participant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:255',
        ]);

        $participant->update($validated);

        return response()->json([
            'data' => $participant,
            'message' => '参加者が更新されました。'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $participant = Participant::findOrFail($id);
        $participant->delete();

        return response()->json([
            'message' => '参加者が削除されました。'
        ], 204);
    }
}
