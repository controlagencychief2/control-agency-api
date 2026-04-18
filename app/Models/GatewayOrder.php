<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayOrder extends Model
{
    protected $fillable = [
        'order_ref',
        'meeting_id',
        'gateway',
        'description',
        'assigned_by',
        'status',
        'completed_at',
        'notes',
        'order_file_written',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'order_file_written' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'meeting_id', 'meeting_id');
    }
}
