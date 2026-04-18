<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HeartbeatController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\BenchmarkController;
use App\Http\Controllers\Api\AgentController;

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
    Route::post('/meetings/{meeting_id}/rounds', [MeetingController::class, 'addRound']);
    Route::post('/meetings/{meeting_id}/complete', [MeetingController::class, 'complete']);

    // Agent polling
    Route::get('/agents/{agent}/pending', [AgentController::class, 'pending']);

    // Benchmark endpoints
    Route::post('/benchmarks', [BenchmarkController::class, 'store']);
    Route::get('/benchmarks/latest', [BenchmarkController::class, 'latestByModel']);
    Route::get('/benchmarks', [BenchmarkController::class, 'index']);
});
