<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Create a new user
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);

// Login a user
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    // Checklists
    Route::apiResource('checklists', App\Http\Controllers\Api\ChecklistController::class);
    Route::apiResource('checklists/{checklist}/items', App\Http\Controllers\Api\ChecklistItemController::class);
    Route::post('checklist-item/{id}/toggle', [App\Http\Controllers\Api\ChecklistItemController::class, 'toggleComplete']);
});
