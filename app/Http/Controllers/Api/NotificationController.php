<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifications = Notification::with(['user', 'reads'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) use ($user) {
                return [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'type' => $notification->type,
                    'author' => $notification->user ? $notification->user->name : '管理者',
                    'is_read' => $user ? $notification->isReadBy($user) : false,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::whereDoesntHave('reads', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Store a newly created notification
     */
    public function store(Request $request): JsonResponse
    {
        // Only admins can create notifications
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'お知らせを投稿する権限がありません。管理者のみが投稿できます。'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'in:info,warning,success,error',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['type'] = $validated['type'] ?? 'info';

        $notification = Notification::create($validated);
        $notification->load('user');

        return response()->json([
            'data' => $notification,
            'message' => 'お知らせを投稿しました。'
        ], 201);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }

        $notification = Notification::findOrFail($id);
        $notification->markAsReadBy($user);

        return response()->json([
            'message' => 'お知らせを既読にしました。'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => '認証が必要です。'
            ], 401);
        }

        $notifications = Notification::whereDoesntHave('reads', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        foreach ($notifications as $notification) {
            $notification->markAsReadBy($user);
        }

        return response()->json([
            'message' => 'すべてのお知らせを既読にしました。'
        ]);
    }

    /**
     * Remove the specified notification
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        
        // Only the author can delete their notification
        if (!$request->user() || $notification->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'お知らせを削除する権限がありません。投稿者のみが削除できます。'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'お知らせを削除しました。'
        ], 204);
    }
}
