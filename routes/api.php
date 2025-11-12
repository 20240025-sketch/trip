<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\DayController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ScheduleItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    
    // Plans - store, update, delete require auth
    Route::post('plans', [PlanController::class, 'store']);
    Route::put('plans/{plan}', [PlanController::class, 'update']);
    Route::delete('plans/{plan}', [PlanController::class, 'destroy']);
    
    // Days
    Route::get('plans/{plan}/days', [DayController::class, 'index']);
    Route::post('plans/{plan}/days', [DayController::class, 'store']);
    Route::apiResource('days', DayController::class)->except(['index', 'store']);
    
    // Schedule Items
    Route::get('days/{day}/schedule-items', [ScheduleItemController::class, 'index']);
    Route::post('days/{day}/schedule-items', [ScheduleItemController::class, 'store']);
    Route::put('schedule-items/{scheduleItem}/reorder', [ScheduleItemController::class, 'reorder']);
    Route::apiResource('schedule-items', ScheduleItemController::class)->except(['index', 'store']);
    
    // Participants
    Route::get('plans/{plan}/participants', [ParticipantController::class, 'index']);
    Route::post('plans/{plan}/participants', [ParticipantController::class, 'store']);
    Route::apiResource('participants', ParticipantController::class)->except(['index', 'store']);
    
    // Checklist Items
    Route::get('plans/{plan}/checklist-items', [ChecklistItemController::class, 'index']);
    Route::post('plans/{plan}/checklist-items', [ChecklistItemController::class, 'store']);
    Route::put('checklist-items/{checklistItem}/toggle', [ChecklistItemController::class, 'toggle']);
    Route::apiResource('checklist-items', ChecklistItemController::class)->except(['index', 'store']);
    
    // Images
    Route::post('images/upload', [ImageController::class, 'upload']);
    Route::put('images/{image}', [ImageController::class, 'update']);
    Route::delete('images/{image}', [ImageController::class, 'destroy']);
});

// Plans - index and show are public
Route::get('plans', [PlanController::class, 'index']);
Route::get('plans/{plan}', [PlanController::class, 'show']);
Route::get('plans/slug/{slug}', [PlanController::class, 'showBySlug']);

// PDF - public access
Route::get('plans/{plan}/pdf', [PdfController::class, 'generate']);
Route::get('plans/{plan}/pdf/preview', [PdfController::class, 'preview']);

// Q&A - public read, authenticated write
Route::get('questions', [QuestionController::class, 'index']);
Route::get('questions/{question}', [QuestionController::class, 'show']);
Route::post('questions', [QuestionController::class, 'store']); // Can post without auth but with auth gets user info
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('questions/{question}', [QuestionController::class, 'destroy']);
    Route::post('questions/{question}/answers', [QuestionController::class, 'storeAnswer']);
});

