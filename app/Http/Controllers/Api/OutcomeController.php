<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingOutcome;
use Illuminate\Http\Request;

class OutcomeController extends Controller
{
    // GET /api/meetings/{meeting_id}/outcomes
    public function index($meetingId)
    {
        Meeting::where('meeting_id', $meetingId)->firstOrFail();

        $outcomes = MeetingOutcome::where('meeting_id', $meetingId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'meeting_id' => $meetingId,
            'data' => $outcomes,
        ]);
    }

    // POST /api/meetings/{meeting_id}/outcomes
    public function store(Request $request, $meetingId)
    {
        Meeting::where('meeting_id', $meetingId)->firstOrFail();

        $validated = $request->validate([
            'outcome_type' => 'required|string|max:50',
            'description' => 'required|string',
            'assigned_to' => 'nullable|string|max:50',
            'gateway' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
        ]);

        $validated['meeting_id'] = $meetingId;

        $outcome = MeetingOutcome::create($validated);

        return response()->json([
            'success' => true,
            'data' => $outcome,
        ], 201);
    }

    // PATCH /api/outcomes/{id}
    public function update(Request $request, $id)
    {
        $outcome = MeetingOutcome::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|max:20',
            'assigned_to' => 'nullable|string|max:50',
            'gateway' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $outcome->update($validated);

        return response()->json([
            'success' => true,
            'data' => $outcome->fresh(),
        ]);
    }
}
