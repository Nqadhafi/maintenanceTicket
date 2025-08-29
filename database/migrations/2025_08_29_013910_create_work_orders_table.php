<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wo')->unique(); // WO-YYYY-XXXX
            $table->enum('type', ['CORRECTIVE','PREVENTIVE']);

            $table->foreignId('ticket_id')->nullable()
                  ->constrained('tickets')->nullOnDelete(); // null jika PM
            $table->foreignId('asset_id')
                  ->constrained('assets')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()
          ->constrained('users')->nullOnDelete();

            $table->enum('status', ['OPEN','IN_PROGRESS','DONE'])->default('OPEN');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->decimal('cost_total', 14, 2)->default(0);
            $table->text('ringkasan_pekerjaan')->nullable();

            $table->timestamps();

            $table->index(['type','status','assignee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
