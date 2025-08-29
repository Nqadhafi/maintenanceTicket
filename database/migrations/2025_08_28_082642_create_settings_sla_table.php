<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsSlaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('settings_sla', function (Blueprint $table) {
        $table->id();
        $table->enum('divisi', ['IT','PRODUKSI','GA']);
        $table->enum('urgensi', ['RENDAH','SEDANG','TINGGI','DARURAT']);
        $table->unsignedInteger('target_duration_minutes'); // contoh: 480 = 8 jam kerja
        $table->json('jam_kerja_json')->nullable(); // opsional override
        $table->timestamps();
        $table->unique(['divisi','urgensi']);
    });
}
public function down(): void { Schema::dropIfExists('settings_sla'); }
}
