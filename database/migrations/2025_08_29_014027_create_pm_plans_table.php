<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pm_plans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_plan');
            $table->foreignId('asset_category_id')
                  ->constrained('asset_categories')->cascadeOnDelete();
            $table->enum('interval_type', ['DAY','WEEK','MONTH','METER']);
            $table->unsignedInteger('interval_value')->default(1); // contoh: setiap 1 bulan/hari/minggu
            $table->json('checklist'); // array string
            $table->foreignId('default_assignee_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['asset_category_id','interval_type','aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_plans');
    }
};
