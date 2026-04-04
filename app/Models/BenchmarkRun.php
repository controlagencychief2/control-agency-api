<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenchmarkRun extends Model
{
    protected $fillable = [
        'machine',
        'model',
        'test_name',
        'tokens_per_sec',
        'tokens_per_sec_std',
        'peak_tokens_per_sec',
        'ttfr_ms',
        'est_ppt_ms',
        'e2e_ttft_ms',
        'pp_tokens',
        'tg_tokens',
        'depth_tokens',
        'runs',
        'benchy_version',
        'notes',
    ];

    protected $casts = [
        'tokens_per_sec'      => 'float',
        'tokens_per_sec_std'  => 'float',
        'peak_tokens_per_sec' => 'float',
        'ttfr_ms'             => 'float',
        'est_ppt_ms'          => 'float',
        'e2e_ttft_ms'         => 'float',
        'pp_tokens'           => 'integer',
        'tg_tokens'           => 'integer',
        'depth_tokens'        => 'integer',
        'runs'                => 'integer',
    ];
}
