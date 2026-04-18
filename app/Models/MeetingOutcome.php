<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingOutcome extends Model
{
    protected $fillable = [
        'meeting_id',
        'outcome_type',
        'description',
        'assigned_to',
        'gateway',
        'due_date',
        'status',
        'order_file_written',
    ];

    protected $casts = [
        'due_date' => 'date',
        'order_file_written' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'meeting_id', 'meeting_id');
    }
}
