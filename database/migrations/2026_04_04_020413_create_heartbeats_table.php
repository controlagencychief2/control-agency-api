<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name', 50)->index();
            $table->string('status', 20)->index(); // 'OK', 'WARN', 'ALERT'
            $table->string('model_tier', 20); // 'primary', 'fallback', 'cloud'
            $table->string('model_name', 100);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heartbeats');
    }
};
