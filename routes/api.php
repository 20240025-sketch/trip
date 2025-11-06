<?php

use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\DayController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\ScheduleItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Plans
Route::apiResource('plans', PlanController::class);
Route::get('plans/slug/{slug}', [PlanController::class, 'showBySlug']);

// Days (nested under plans)
Route::get('plans/{plan}/days', [DayController::class, 'index']);
Route::post('plans/{plan}/days', [DayController::class, 'store']);
Route::apiResource('days', DayController::class)->except(['index', 'store']);

// Schedule Items (nested under days)
Route::get('days/{day}/schedule-items', [ScheduleItemController::class, 'index']);
Route::post('days/{day}/schedule-items', [ScheduleItemController::class, 'store']);
Route::put('schedule-items/{scheduleItem}/reorder', [ScheduleItemController::class, 'reorder']);
Route::apiResource('schedule-items', ScheduleItemController::class)->except(['index', 'store']);

// Participants (nested under plans)
Route::get('plans/{plan}/participants', [ParticipantController::class, 'index']);
Route::post('plans/{plan}/participants', [ParticipantController::class, 'store']);
Route::apiResource('participants', ParticipantController::class)->except(['index', 'store']);

// Checklist Items (nested under plans)
Route::get('plans/{plan}/checklist-items', [ChecklistItemController::class, 'index']);
Route::post('plans/{plan}/checklist-items', [ChecklistItemController::class, 'store']);
Route::put('checklist-items/{checklistItem}/toggle', [ChecklistItemController::class, 'toggle']);
Route::apiResource('checklist-items', ChecklistItemController::class)->except(['index', 'store']);

// Images
Route::post('images/upload', [ImageController::class, 'upload']);
Route::put('images/{image}', [ImageController::class, 'update']);
Route::delete('images/{image}', [ImageController::class, 'destroy']);

// PDF
Route::get('plans/{plan}/pdf', [PdfController::class, 'generate']);
Route::get('plans/{plan}/pdf/preview', [PdfController::class, 'preview']);

