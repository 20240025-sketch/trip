<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions with answers
     */
    public function index(): JsonResponse
    {
        $questions = Question::with(['user', 'answers.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($questions);
    }

    /**
     * Store a new question
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        $validated['user_id'] = $request->user() ? $request->user()->id : null;

        $question = Question::create($validated);
        $question->load(['user', 'answers']);

        return response()->json([
            'data' => $question,
            'message' => '質問を投稿しました。'
        ], 201);
    }

    /**
     * Display the specified question
     */
    public function show(string $id): JsonResponse
    {
        $question = Question::with(['user', 'answers.user'])
            ->findOrFail($id);

        return response()->json([
            'data' => $question
        ]);
    }

    /**
     * Delete a question
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $question = Question::findOrFail($id);

        // Only admin or question owner can delete
        if (!$request->user() || 
            (!$request->user()->isAdmin() && $question->user_id !== $request->user()->id)) {
            return response()->json([
                'message' => 'この質問を削除する権限がありません。'
            ], 403);
        }

        $question->delete();

        return response()->json([
            'message' => '質問を削除しました。'
        ], 204);
    }

    /**
     * Store an answer to a question
     */
    public function storeAnswer(Request $request, string $questionId): JsonResponse
    {
        // Only admins can answer
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => '回答する権限がありません。管理者のみが回答できます。'
            ], 403);
        }

        $question = Question::findOrFail($questionId);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['question_id'] = $question->id;

        $answer = Answer::create($validated);
        $answer->load('user');

        return response()->json([
            'data' => $answer,
            'message' => '回答を投稿しました。'
        ], 201);
    }
}
