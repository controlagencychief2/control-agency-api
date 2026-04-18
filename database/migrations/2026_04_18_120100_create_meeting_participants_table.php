<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id', 100)->index();
            $table->string('agent_name', 50);
            $table->unsignedInteger('turn_order');
            $table->boolean('responded')->default(false);
            $table->timestamps();

            $table->unique(['meeting_id', 'agent_name']);

            $table->foreign('meeting_id')
                  ->references('meeting_id')
                  ->on('meetings')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
    }
};
