<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,user',
        ]);

        // メールアドレスが数字で始まらず、@seiei.ac.jpで終わる場合は管理者（admin）
        // それ以外は利用者（user）として登録
        if (preg_match('/^\d/', $validated['email']) === 0 && 
            str_ends_with($validated['email'], '@seiei.ac.jp')) {
            $role = 'admin';
        } else {
            $role = 'user';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'ユーザー登録が完了しました。'
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'ログインしました。'
        ]);
    }

    /**
     * Admin login
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'admin_password' => 'required',
        ]);

        // Check admin password first
        if ($request->admin_password !== '0835385252') {
            throw ValidationException::withMessages([
                'admin_password' => ['管理者パスワードが正しくありません。'],
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Check if user has admin privileges based on email
        // Admin: email does NOT start with a digit AND ends with @seiei.ac.jp
        if (!$user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['管理者権限がありません。メールアドレスが条件を満たしていません。'],
            ]);
        }

        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => '管理者としてログインしました。'
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ログアウトしました。'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()
        ]);
    }
}
