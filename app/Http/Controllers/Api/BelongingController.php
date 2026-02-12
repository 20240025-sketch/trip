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
    public function index(Request $request, Plan $plan): JsonResponse
    {
        // Check if user can view this plan
        $user = $request->user();
        if (!$plan->canView($user)) {
            return response()->json([
                'message' => 'この旅行計画を閲覧する権限がありません。'
            ], 403);
        }

        $belongings = $plan->belongings()->orderBy('order')->get();

        // 各持ち物に対して、現在のユーザーのチェック状態を追加
        $belongingsWithUserCheck = $belongings->map(function ($belonging) use ($user) {
            $belongingArray = $belonging->toArray();
            
            // ログインしている場合のみ、そのユーザーのチェック状態を追加
            if ($user) {
                $userCheck = $belonging->users()
                    ->where('user_id', $user->id)
                    ->first();
                
                $belongingArray['user_is_checked'] = $userCheck ? $userCheck->pivot->is_checked : false;
            } else {
                $belongingArray['user_is_checked'] = false;
            }
            
            return $belongingArray;
        });

        return response()->json([
            'data' => $belongingsWithUserCheck
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
    public function toggle(Request $request, Belonging $belonging): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'ログインが必要です。'
            ], 401);
        }

        // 現在のユーザーのチェック状態を取得または作成
        $userCheck = $belonging->users()->where('user_id', $user->id)->first();
        
        if ($userCheck) {
            // 既存のチェック状態をトグル
            $belonging->users()->updateExistingPivot($user->id, [
                'is_checked' => !$userCheck->pivot->is_checked
            ]);
            $isChecked = !$userCheck->pivot->is_checked;
        } else {
            // 新しいチェック状態を作成（デフォルトでチェック済み）
            $belonging->users()->attach($user->id, ['is_checked' => true]);
            $isChecked = true;
        }

        return response()->json([
            'data' => [
                'id' => $belonging->id,
                'user_is_checked' => $isChecked,
            ],
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
