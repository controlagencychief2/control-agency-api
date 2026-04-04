<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_runs', function (Blueprint $table) {
            $table->id();
            $table->string('machine', 50);           // JEDHA, TATOOINE, CONTROLAGENCY
            $table->string('model', 100);             // llama3.1:8b, deepseek-r1:70b, etc.
            $table->string('test_name', 100);         // pp512, tg64, pp512 @ d2048
            $table->float('tokens_per_sec')->nullable();
            $table->float('tokens_per_sec_std')->nullable();
            $table->float('peak_tokens_per_sec')->nullable();
            $table->float('ttfr_ms')->nullable();     // time to first response (ms)
            $table->float('est_ppt_ms')->nullable();  // estimated prompt processing time (ms)
            $table->float('e2e_ttft_ms')->nullable(); // end-to-end time to first token (ms)
            $table->integer('pp_tokens')->nullable(); // prompt length in tokens
            $table->integer('tg_tokens')->nullable(); // generation length in tokens
            $table->integer('depth_tokens')->nullable(); // context depth (0 = none)
            $table->integer('runs')->nullable();      // iterations averaged
            $table->string('benchy_version', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['machine', 'model']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_runs');
    }
};
