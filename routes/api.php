<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatSessionController;

// Rutas para sesiones del chat
Route::prefix('chat-sessions')->group(function () {
    Route::post('/', [ChatSessionController::class, 'getOrCreate']);
    Route::get('/{userId}', [ChatSessionController::class, 'show']);
    Route::put('/{userId}', [ChatSessionController::class, 'update']);
    Route::delete('/{userId}', [ChatSessionController::class, 'destroy']);
    Route::get('/active', [ChatSessionController::class, 'activeSessions']);
    Route::post('/cleanup', [ChatSessionController::class, 'cleanup']);
});