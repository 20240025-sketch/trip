<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::with('user');
        $user = $request->user();

        if ($user) {
            if ($user->isAdmin()) {
                // Admins can see all plans
                // No filtering needed
            } elseif ($user->isRegularUser()) {
                // Regular users can see:
                // 1. Their own plans
                // 2. Plans from same team
                // 3. Public plans from other teams
                $teamId = $user->getTeamId();
                
                $query->where(function ($q) use ($user, $teamId) {
                    // Own plans
                    $q->where('user_id', $user->id);
                    
                    // Same team plans
                    if ($teamId) {
                        $q->orWhereHas('user', function ($userQuery) use ($teamId) {
                            $userQuery->where('email', 'like', $teamId . '%');
                        });
                    }
                    
                    // Public plans
                    $q->orWhere('is_public', true);
                });
            } else {
                // Other authenticated users (no team)
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('is_public', true);
                });
            }
        } else {
            // Not authenticated - only public plans
            $query->where('is_public', true);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by public status
        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        // Order by start_date descending
        $query->orderBy('start_date', 'desc');

        // Paginate results
        $plans = $query->paginate(12);

        return response()->json([
            'data' => PlanResource::collection($plans),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page' => $plans->lastPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        
        $plan = Plan::create($validated);

        // Create days based on date range
        $startDate = $plan->start_date;
        $endDate = $plan->end_date;
        $dayNumber = 1;

        while ($startDate <= $endDate) {
            $plan->days()->create([
                'date' => $startDate,
                'day_number' => $dayNumber,
                'title' => "Day {$dayNumber}",
            ]);

            $startDate = $startDate->addDay();
            $dayNumber++;
        }

        $plan->load('days');

        return response()->json([
            'data' => new PlanResource($plan),
            'message' => '計画が作成されました。'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $plan = Plan::with([
            'days.scheduleItems.images',
            'participants',
            'checklistItems',
            'images',
            'user'
        ])->findOrFail($id);

        // Check if user can view this plan
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'この計画を閲覧する権限がありません。'
            ], 403);
        }

        return response()->json([
            'data' => new PlanResource($plan)
        ]);
    }

    /**
     * Display the specified resource by slug.
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        $plan = Plan::where('slug', $slug)
            ->with([
                'days.scheduleItems.images',
                'participants',
                'checklistItems',
                'images',
                'user'
            ])
            ->firstOrFail();

        // Check if user can view this plan
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'この計画を閲覧する権限がありません。'
            ], 403);
        }

        return response()->json([
            'data' => new PlanResource($plan)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanRequest $request, string $id): JsonResponse
    {
        $plan = Plan::with('user')->findOrFail($id);
        
        // Check if user can edit this plan
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'この計画を編集する権限がありません。'
            ], 403);
        }
        
        $plan->update($request->validated());

        // If dates changed, update days
        if ($request->has('start_date') || $request->has('end_date')) {
            // Delete existing days
            $plan->days()->delete();

            // Recreate days
            $startDate = $plan->start_date;
            $endDate = $plan->end_date;
            $dayNumber = 1;

            while ($startDate <= $endDate) {
                $plan->days()->create([
                    'date' => $startDate,
                    'day_number' => $dayNumber,
                    'title' => "Day {$dayNumber}",
                ]);

                $startDate = $startDate->addDay();
                $dayNumber++;
            }
        }

        $plan->load('days');

        return response()->json([
            'data' => new PlanResource($plan),
            'message' => '計画が更新されました。'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $plan = Plan::with('user')->findOrFail($id);
        
        // Check if user can delete this plan
        if (!$plan->canDelete($request->user())) {
            return response()->json([
                'message' => 'この計画を削除する権限がありません。'
            ], 403);
        }
        
        $plan->delete();

        return response()->json([
            'message' => '計画が削除されました。'
        ], 204);
    }
}
