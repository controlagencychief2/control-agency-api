<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id', 100)->unique()->index();
            $table->string('meeting_type', 50)->index(); // 'daily-standup', 'emergency', etc
            $table->string('status', 20)->default('active')->index(); // 'active', 'closed'
            $table->text('topic')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
