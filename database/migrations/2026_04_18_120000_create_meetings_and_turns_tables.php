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
            $table->string('meeting_id', 100)->unique();
            $table->string('meeting_type', 50)->index();
            $table->text('topic')->nullable();
            $table->string('tone', 20)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('current_round')->default(1);
            $table->unsignedInteger('max_rounds')->default(2);
            $table->string('current_turn', 50)->nullable()->index();
            $table->unsignedInteger('current_turn_index')->default(0);
            $table->string('telegram_group_id', 50)->nullable();
            $table->string('telegram_thread_id', 50)->nullable();
            $table->text('initial_message')->nullable();
            $table->string('created_by', 50)->default('Number 2');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_turns', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id', 100)->index();
            $table->string('agent_name', 50);
            $table->unsignedInteger('round_number');
            $table->unsignedInteger('turn_index');
            $table->longText('content');
            $table->unsignedInteger('token_count')->default(0);
            $table->string('model_used', 100)->nullable();
            $table->boolean('local_model')->default(false);
            $table->boolean('posted_to_telegram')->default(false);
            $table->string('telegram_message_id', 50)->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')
                  ->references('meeting_id')
                  ->on('meetings')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_turns');
        Schema::dropIfExists('meetings');
    }
};
