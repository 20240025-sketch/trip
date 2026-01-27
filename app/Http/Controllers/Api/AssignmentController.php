<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Get current user's assignment info
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'class' => $user->class,
            'number' => $user->number,
            'room_day1' => $user->room_day1,
            'room_day2' => $user->room_day2,
            'room_day3' => $user->room_day3,
            'bus_number' => $user->bus_number,
            'role' => $user->role,
        ]);
    }

    /**
     * Get all users' assignment info (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => '管理者のみがすべてのユーザー情報を閲覧できます。'
            ], 403);
        }

        $users = User::select('id', 'name', 'email', 'class', 'number', 'room_day1', 'room_day2', 'room_day3', 'bus_number', 'role')
            ->orderBy('role', 'desc') // admin first
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Update user's assignment info (admin only)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }

        if ($authUser->role !== 'admin') {
            return response()->json([
                'message' => '管理者のみがユーザー情報を編集できます。'
            ], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'class' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
            'room_day1' => 'nullable|string|max:255',
            'room_day2' => 'nullable|string|max:255',
            'room_day3' => 'nullable|string|max:255',
            'bus_number' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'ユーザー情報を更新しました。',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'class' => $user->class,
                'number' => $user->number,
                'room_day1' => $user->room_day1,
                'room_day2' => $user->room_day2,
                'room_day3' => $user->room_day3,
                'bus_number' => $user->bus_number,
                'role' => $user->role,
            ],
        ]);
    }
}
