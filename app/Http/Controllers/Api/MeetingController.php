<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\MeetingTurn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{
    // POST /api/meetings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_id' => 'required|string|max:100|unique:meetings,meeting_id',
            'meeting_type' => 'required|string|max:50',
            'topic' => 'nullable|string',
            'tone' => 'nullable|string|max:20',
            'max_rounds' => 'nullable|integer|min:1|max:999',
            'telegram_group_id' => 'nullable|string|max:50',
            'telegram_thread_id' => 'nullable|string|max:50',
            'initial_message' => 'nullable|string',
            'created_by' => 'nullable|string|max:50',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|string|max:50',
        ]);

        $participants = $validated['participants'];
        unset($validated['participants']);

        $meeting = DB::transaction(function () use ($validated, $participants) {
            $meeting = Meeting::create(array_merge($validated, [
                'status' => 'in_progress',
                'current_round' => 1,
                'max_rounds' => $validated['max_rounds'] ?? 2,
                'current_turn' => $participants[0],
                'current_turn_index' => 0,
                'started_at' => now(),
            ]));

            foreach ($participants as $index => $agentName) {
                MeetingParticipant::create([
                    'meeting_id' => $meeting->meeting_id,
                    'agent_name' => $agentName,
                    'turn_order' => $index,
                    'responded' => false,
                ]);
            }

            return $meeting->fresh(['participants']);
        });

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ], 201);
    }

    // GET /api/meetings/{meeting_id}
    public function show($meetingId)
    {
        $meeting = Meeting::where('meeting_id', $meetingId)
            ->with(['participants', 'turns', 'outcomes'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ]);
    }

    // POST /api/meetings/{meeting_id}/turns
    // Atomic: validate current_turn, record turn, advance pointer.
    public function storeTurn(Request $request, $meetingId)
    {
        $validated = $request->validate([
            'agent_name' => 'required|string|max:50',
            'content' => 'required|string',
            'token_count' => 'nullable|integer|min:0',
            'model_used' => 'nullable|string|max:100',
            'local_model' => 'nullable|boolean',
            'posted_to_telegram' => 'nullable|boolean',
            'telegram_message_id' => 'nullable|string|max:50',
        ]);

        $result = DB::transaction(function () use ($meetingId, $validated) {
            $meeting = Meeting::where('meeting_id', $meetingId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($meeting->status !== 'in_progress') {
                abort(409, "Meeting is not in progress (status: {$meeting->status})");
            }

            if ($meeting->current_turn !== $validated['agent_name']) {
                abort(409, "Not {$validated['agent_name']}'s turn (current: {$meeting->current_turn})");
            }

            $turnOrder = MeetingParticipant::where('meeting_id', $meetingId)
                ->orderBy('turn_order')
                ->pluck('agent_name')
                ->all();

            $turn = MeetingTurn::create([
                'meeting_id' => $meetingId,
                'agent_name' => $validated['agent_name'],
                'round_number' => $meeting->current_round,
                'turn_index' => $meeting->current_turn_index,
                'content' => $validated['content'],
                'token_count' => $validated['token_count'] ?? 0,
                'model_used' => $validated['model_used'] ?? null,
                'local_model' => $validated['local_model'] ?? false,
                'posted_to_telegram' => $validated['posted_to_telegram'] ?? false,
                'telegram_message_id' => $validated['telegram_message_id'] ?? null,
            ]);

            MeetingParticipant::where('meeting_id', $meetingId)
                ->where('agent_name', $validated['agent_name'])
                ->update(['responded' => true]);

            $nextIndex = $meeting->current_turn_index + 1;
            $nextRound = $meeting->current_round;
            $nextAgent = null;
            $nextStatus = 'in_progress';

            if ($nextIndex < count($turnOrder)) {
                $nextAgent = $turnOrder[$nextIndex];
            } elseif ($nextRound < $meeting->max_rounds) {
                $nextRound = $nextRound + 1;
                $nextIndex = 0;
                $nextAgent = $turnOrder[0];
            } else {
                $nextIndex = $meeting->current_turn_index;
                $nextStatus = 'pending_summary';
            }

            $meeting->update([
                'current_turn' => $nextAgent,
                'current_turn_index' => $nextIndex,
                'current_round' => $nextRound,
                'status' => $nextStatus,
            ]);

            return [
                'turn' => $turn,
                'next_agent' => $nextAgent,
                'next_round' => $nextRound,
                'status' => $nextStatus,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result['turn'],
            'next_agent' => $result['next_agent'],
            'next_round' => $result['next_round'],
            'status' => $result['status'],
        ], 201);
    }

    // GET /api/meetings/{meeting_id}/turns
    public function turns($meetingId)
    {
        Meeting::where('meeting_id', $meetingId)->firstOrFail();

        $turns = MeetingTurn::where('meeting_id', $meetingId)
            ->orderBy('round_number')
            ->orderBy('turn_index')
            ->get();

        return response()->json([
            'success' => true,
            'meeting_id' => $meetingId,
            'data' => $turns,
        ]);
    }

    // POST /api/meetings/{meeting_id}/rounds
    public function addRound(Request $request, $meetingId)
    {
        $validated = $request->validate([
            'focus' => 'nullable|string',
        ]);

        $meeting = DB::transaction(function () use ($meetingId) {
            $meeting = Meeting::where('meeting_id', $meetingId)
                ->lockForUpdate()
                ->firstOrFail();

            $firstAgent = MeetingParticipant::where('meeting_id', $meetingId)
                ->orderBy('turn_order')
                ->value('agent_name');

            $meeting->update([
                'max_rounds' => $meeting->max_rounds + 1,
                'current_round' => $meeting->current_round + 1,
                'current_turn' => $firstAgent,
                'current_turn_index' => 0,
                'status' => 'in_progress',
            ]);

            return $meeting->fresh();
        });

        return response()->json([
            'success' => true,
            'data' => $meeting,
            'focus' => $validated['focus'] ?? null,
        ]);
    }

    // POST /api/meetings/{meeting_id}/complete
    public function complete(Request $request, $meetingId)
    {
        $meeting = Meeting::where('meeting_id', $meetingId)->firstOrFail();

        $meeting->update([
            'status' => 'completed',
            'completed_at' => now(),
            'current_turn' => null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $meeting->fresh(),
        ]);
    }
}
