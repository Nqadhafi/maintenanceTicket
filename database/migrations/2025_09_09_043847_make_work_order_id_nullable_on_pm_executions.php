<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Jika pakai constraint default Laravel, namanya biasanya: pm_executions_work_order_id_foreign
        Schema::table('pm_executions', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
        });

        // Kalau tidak pakai doctrine/dbal, pakai raw SQL (MySQL)
        DB::statement('ALTER TABLE pm_executions MODIFY work_order_id BIGINT UNSIGNED NULL');

        Schema::table('pm_executions', function (Blueprint $table) {
            $table->foreign('work_order_id')
                  ->references('id')->on('work_orders')
                  ->nullOnDelete(); // ON DELETE SET NULL
        });
    }

    public function down(): void
    {
        Schema::table('pm_executions', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
        });

        // Balik lagi jadi NOT NULL (sesuaikan jika perlu)
        DB::statement('ALTER TABLE pm_executions MODIFY work_order_id BIGINT UNSIGNED NOT NULL');

        Schema::table('pm_executions', function (Blueprint $table) {
            $table->foreign('work_order_id')
                  ->references('id')->on('work_orders')
                  ->restrictOnDelete();
        });
    }
};
