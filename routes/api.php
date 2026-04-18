<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HeartbeatController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\BenchmarkController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\OutcomeController;
use App\Http\Controllers\Api\GatewayOrderController;

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

    // Meeting outcomes
    Route::get('/meetings/{meeting_id}/outcomes', [OutcomeController::class, 'index']);
    Route::post('/meetings/{meeting_id}/outcomes', [OutcomeController::class, 'store']);
    Route::patch('/outcomes/{id}', [OutcomeController::class, 'update']);

    // Gateway orders
    Route::post('/gateway-orders', [GatewayOrderController::class, 'store']);
    Route::patch('/gateway-orders/{id}', [GatewayOrderController::class, 'update']);
    Route::get('/gateways/{gateway}/orders', [GatewayOrderController::class, 'indexByGateway']);

    // Agent polling
    Route::get('/agents/{agent}/pending', [AgentController::class, 'pending']);

    // Benchmark endpoints
    Route::post('/benchmarks', [BenchmarkController::class, 'store']);
    Route::get('/benchmarks/latest', [BenchmarkController::class, 'latestByModel']);
    Route::get('/benchmarks', [BenchmarkController::class, 'index']);
});
