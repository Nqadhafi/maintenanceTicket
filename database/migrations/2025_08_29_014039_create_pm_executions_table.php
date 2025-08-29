<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pm_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_schedule_id')
                  ->constrained('pm_schedules')->cascadeOnDelete();
            $table->foreignId('work_order_id')
                  ->constrained('work_orders')->cascadeOnDelete();

            $table->dateTime('performed_at');
            $table->json('checklist_result')->nullable(); // hasil centang checklist
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['pm_schedule_id','performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_executions');
    }
};
