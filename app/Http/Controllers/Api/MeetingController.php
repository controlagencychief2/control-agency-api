<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingTurn;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    // POST /api/meetings - Create a meeting
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_id' => 'required|string|max:100|unique:meetings,meeting_id',
            'meeting_type' => 'required|string|max:50',
            'topic' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $meeting = Meeting::create($validated);

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ], 201);
    }

    // GET /api/meetings/{meeting_id} - Get meeting + all turns
    public function show($meetingId)
    {
        $meeting = Meeting::where('meeting_id', $meetingId)
            ->with('turns')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ]);
    }

    // POST /api/meetings/{meeting_id}/turns - Add a turn
    public function storeTurn(Request $request, $meetingId)
    {
        $meeting = Meeting::where('meeting_id', $meetingId)->firstOrFail();

        $validated = $request->validate([
            'agent_name' => 'required|string|max:50',
            'round_number' => 'required|integer',
            'content' => 'required|string',
        ]);

        $validated['meeting_id'] = $meetingId;

        $turn = MeetingTurn::create($validated);

        return response()->json([
            'success' => true,
            'data' => $turn,
        ], 201);
    }

    // GET /api/meetings/{meeting_id}/turns - List all turns
    public function turns($meetingId)
    {
        $meeting = Meeting::where('meeting_id', $meetingId)->firstOrFail();

        $turns = MeetingTurn::where('meeting_id', $meetingId)
            ->orderBy('round_number')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'meeting_id' => $meetingId,
            'data' => $turns,
        ]);
    }
}
