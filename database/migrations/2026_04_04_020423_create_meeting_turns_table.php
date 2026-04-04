<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_turns', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id', 100)->index();
            $table->string('agent_name', 50);
            $table->integer('round_number');
            $table->longText('content');
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
    }
};
