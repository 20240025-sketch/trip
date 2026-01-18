<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::with('user');
        
        // Try to authenticate if token is provided (since this route is public)
        $user = null;
        if ($token = $request->bearerToken()) {
            try {
                // Manually authenticate using Sanctum
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $user = $personalAccessToken->tokenable;
                }
            } catch (\Exception $e) {
                Log::error('Token authentication failed', ['error' => $e->getMessage()]);
            }
        }

        Log::info('Plans Index Request', [
            'has_bearer_token' => $request->bearerToken() !== null,
            'token_length' => $request->bearerToken() ? strlen($request->bearerToken()) : 0,
            'has_user' => $user !== null,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'is_public_filter' => $request->has('is_public') ? $request->boolean('is_public') : 'none',
        ]);

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

        // Filter by public status - but always show user's own plans
        if ($request->has('is_public')) {
            $isPublic = $request->boolean('is_public');
            if ($user) {
                // Authenticated: show filtered plans + user's own plans
                $query->where(function ($q) use ($user, $isPublic) {
                    $q->where('is_public', $isPublic)
                      ->orWhere('user_id', $user->id);
                });
            } else {
                // Not authenticated: only show public plans
                $query->where('is_public', $isPublic);
            }
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

        // Refresh the plan to ensure all data is loaded
        $plan->refresh();
        $plan->load('user');

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

        $user = $request->user();
        
        // Log for debugging
        Log::info('Plan Show Access Attempt', [
            'plan_id' => $plan->id,
            'plan_user_id' => $plan->user_id,
            'plan_user_id_type' => gettype($plan->user_id),
            'current_user_id' => $user?->id,
            'current_user_id_type' => gettype($user?->id),
            'current_user_email' => $user?->email,
            'is_public' => $plan->is_public,
            'user_exists' => $user !== null,
            'ids_match' => $user && ((int)$plan->user_id === (int)$user->id),
        ]);
        
        // Check if user can view this plan
        $canView = $plan->canView($user);
        $canEdit = $plan->canEdit($user);
        
        Log::info('Permission Check Results', [
            'can_view' => $canView,
            'can_edit' => $canEdit,
        ]);
        
        if (!$canView && !$canEdit) {
            Log::warning('Access Denied', [
                'plan_id' => $plan->id,
                'user_id' => $user?->id,
            ]);
            
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

        // Try to authenticate if token is provided (since this route is public)
        $user = null;
        if ($token = $request->bearerToken()) {
            try {
                // Manually authenticate using Sanctum
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $user = $personalAccessToken->tokenable;
                }
            } catch (\Exception $e) {
                Log::error('Token authentication failed in showBySlug', ['error' => $e->getMessage()]);
            }
        }

        // Check if user can view this plan
        if (!$plan->canView($user)) {
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
