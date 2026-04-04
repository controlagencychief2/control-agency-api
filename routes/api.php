<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HeartbeatController;
use App\Http\Controllers\Api\MeetingController;

// All API routes protected by Bearer token middleware
Route::middleware('agent.api')->group(function () {
    
    // Heartbeat endpoints
    Route::post('/heartbeat', [HeartbeatController::class, 'store']);
    Route::get('/heartbeats', [HeartbeatController::class, 'index']);
    Route::get('/heartbeats/{agent}', [HeartbeatController::class, 'show']);
    
    // Meeting endpoints
    Route::post('/meetings', [MeetingController::class, 'store']);
    Route::get('/meetings/{meeting_id}', [MeetingController::class, 'show']);
    Route::post('/meetings/{meeting_id}/turns', [MeetingController::class, 'storeTurn']);
    Route::get('/meetings/{meeting_id}/turns', [MeetingController::class, 'turns']);
});
