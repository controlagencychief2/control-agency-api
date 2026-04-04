<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BenchmarkRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BenchmarkController extends Controller
{
    // POST /api/benchmarks - Store a benchmark result
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine'             => 'required|string|max:50',
            'model'               => 'required|string|max:100',
            'test_name'           => 'required|string|max:100',
            'tokens_per_sec'      => 'required|numeric',
            'tokens_per_sec_std'  => 'nullable|numeric',
            'peak_tokens_per_sec' => 'nullable|numeric',
            'ttfr_ms'             => 'nullable|numeric',
            'est_ppt_ms'          => 'nullable|numeric',
            'e2e_ttft_ms'         => 'nullable|numeric',
            'pp_tokens'           => 'nullable|integer',
            'tg_tokens'           => 'nullable|integer',
            'depth_tokens'        => 'nullable|integer',
            'runs'                => 'nullable|integer',
            'benchy_version'      => 'nullable|string|max:20',
            'notes'               => 'nullable|string',
        ]);

        $run = BenchmarkRun::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $run,
        ], 201);
    }

    // GET /api/benchmarks - List runs with optional filters
    public function index(Request $request)
    {
        $query = BenchmarkRun::orderBy('created_at', 'desc');

        if ($request->has('machine')) {
            $query->where('machine', $request->machine);
        }

        if ($request->has('model')) {
            $query->where('model', $request->model);
        }

        if ($request->has('test_name')) {
            $query->where('test_name', $request->test_name);
        }

        $runs = $query->limit(200)->get();

        return response()->json([
            'success' => true,
            'count'   => $runs->count(),
            'data'    => $runs,
        ]);
    }

    // GET /api/benchmarks/latest - Summary: latest pp512 + tg64 per (machine, model)
    public function latestByModel()
    {
        $results = BenchmarkRun::select('machine', 'model', 'test_name',
                'tokens_per_sec', 'tokens_per_sec_std', 'peak_tokens_per_sec',
                'ttfr_ms', 'e2e_ttft_ms', 'created_at')
            ->whereIn('test_name', ['pp512', 'tg64'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn($r) => $r->machine . '|' . $r->model)
            ->map(function ($group) {
                $pp  = $group->firstWhere('test_name', 'pp512');
                $tg  = $group->firstWhere('test_name', 'tg64');
                $rep = $group->first();
                return [
                    'machine'    => $rep->machine,
                    'model'      => $rep->model,
                    'last_run'   => $rep->created_at,
                    'pp512_t_s'  => $pp?->tokens_per_sec,
                    'tg64_t_s'   => $tg?->tokens_per_sec,
                    'tg64_ttfr'  => $tg?->ttfr_ms,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
