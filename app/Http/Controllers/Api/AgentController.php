<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;

class AgentController extends Controller
{
    // GET /api/agents/{agent}/pending
    // Meetings currently awaiting this agent's turn. Agents poll this
    // to decide whether to act.
    public function pending($agentName)
    {
        $meetings = Meeting::where('current_turn', $agentName)
            ->where('status', 'in_progress')
            ->with('participants')
            ->orderBy('started_at')
            ->get();

        return response()->json([
            'success' => true,
            'agent' => $agentName,
            'data' => $meetings,
        ]);
    }
}
