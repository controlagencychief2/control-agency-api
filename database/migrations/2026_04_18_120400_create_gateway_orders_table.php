<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_ref', 50)->index();
            $table->string('meeting_id', 100)->nullable()->index();
            $table->string('gateway', 50);
            $table->text('description');
            $table->string('assigned_by', 50)->default('Number 2');
            $table->string('status', 20)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('order_file_written')->default(false);
            $table->timestamps();

            $table->index(['gateway', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_orders');
    }
};
