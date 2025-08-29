<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('assets', function (Blueprint $table) {
        $table->id();
        $table->string('kode_aset')->unique(); // IT-2025-001, MESIN-001, dst
        $table->string('nama');
        $table->foreignId('asset_category_id')->constrained()->cascadeOnDelete();
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
        $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
        $table->json('spesifikasi'); // kunci Indonesia-friendly
        $table->enum('status', ['AKTIF','RUSAK','SCRAP'])->default('AKTIF');
        $table->date('tanggal_beli')->nullable();
        $table->string('lampiran_cover')->nullable(); // path foto/manual cover
        $table->timestamps();

        $table->index(['asset_category_id','status']);
    });
}
public function down(): void { Schema::dropIfExists('assets'); }
}
