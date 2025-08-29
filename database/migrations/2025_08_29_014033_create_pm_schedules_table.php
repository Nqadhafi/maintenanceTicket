<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pm_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_plan_id')
                  ->constrained('pm_plans')->cascadeOnDelete();
            $table->foreignId('asset_id')
                  ->constrained('assets')->cascadeOnDelete();

            $table->dateTime('next_due_at')->nullable();     // untuk DAY/WEEK/MONTH
            $table->unsignedInteger('meter_threshold')->nullable(); // untuk METER
            $table->boolean('aktif')->default(true);

            $table->timestamps();

            $table->unique(['pm_plan_id','asset_id']); // 1 plan per aset
            $table->index(['next_due_at','aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_schedules');
    }
};
