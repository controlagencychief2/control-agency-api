<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'meeting_id',
        'meeting_type',
        'topic',
        'tone',
        'status',
        'current_round',
        'max_rounds',
        'current_turn',
        'current_turn_index',
        'telegram_group_id',
        'telegram_thread_id',
        'initial_message',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'current_round' => 'integer',
        'max_rounds' => 'integer',
        'current_turn_index' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function turns(): HasMany
    {
        return $this->hasMany(MeetingTurn::class, 'meeting_id', 'meeting_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class, 'meeting_id', 'meeting_id');
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(MeetingOutcome::class, 'meeting_id', 'meeting_id');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(MeetingCost::class, 'meeting_id', 'meeting_id');
    }
}
