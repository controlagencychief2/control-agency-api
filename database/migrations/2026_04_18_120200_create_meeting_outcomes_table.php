<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_outcomes', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id', 100)->index();
            $table->string('outcome_type', 50);
            $table->text('description');
            $table->string('assigned_to', 50)->nullable();
            $table->string('gateway', 50)->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('order_file_written')->default(false);
            $table->timestamps();

            $table->foreign('meeting_id')
                  ->references('meeting_id')
                  ->on('meetings')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_outcomes');
    }
};
