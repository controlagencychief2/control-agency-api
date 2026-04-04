<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'meeting_id',
        'meeting_type',
        'status',
        'topic',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function turns(): HasMany
    {
        return $this->hasMany(MeetingTurn::class, 'meeting_id', 'meeting_id');
    }
}
