<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Heartbeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeartbeatController extends Controller
{
    // POST /api/heartbeat - Agent posts status
    public function store(Request $request)
    {
        $validated = $request->validate([
            'agent_name' => 'required|string|max:50',
            'status' => 'required|string|in:OK,WARN,ALERT',
            'model_tier' => 'required|string|max:20',
            'model_name' => 'required|string|max:100',
            'message' => 'nullable|string',
        ]);

        $heartbeat = Heartbeat::create($validated);

        return response()->json([
            'success' => true,
            'data' => $heartbeat,
        ], 201);
    }

    // GET /api/heartbeats - All agents, latest status each
    public function index()
    {
        $latestHeartbeats = Heartbeat::select('heartbeats.*')
            ->from(DB::raw('(SELECT agent_name, MAX(created_at) as max_created_at FROM heartbeats GROUP BY agent_name) as latest'))
            ->join('heartbeats', function($join) {
                $join->on('heartbeats.agent_name', '=', 'latest.agent_name')
                     ->on('heartbeats.created_at', '=', 'latest.max_created_at');
            })
            ->orderBy('heartbeats.agent_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $latestHeartbeats,
        ]);
    }

    // GET /api/heartbeats/{agent} - Agent history (last 50)
    public function show($agentName)
    {
        $heartbeats = Heartbeat::where('agent_name', $agentName)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'agent' => $agentName,
            'data' => $heartbeats,
        ]);
    }
}
