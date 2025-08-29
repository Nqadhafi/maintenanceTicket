<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->string('kode_tiket')->unique(); // TCK-YYYY-XXXX
        $table->foreignId('user_id')->constrained('users'); // pelapor

        $table->enum('kategori', ['IT','PRODUKSI','GA','LAINNYA']);
        $table->enum('urgensi', ['RENDAH','SEDANG','TINGGI','DARURAT']);

        $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
        $table->boolean('is_asset_unlisted')->default(false);
        $table->string('asset_nama_manual')->nullable();
        $table->string('asset_lokasi_manual')->nullable();
        $table->string('asset_vendor_manual')->nullable();

        $table->enum('divisi_pj', ['IT','PRODUKSI','GA']); // acuan SLA & visibilitas PJ
        $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete(); // harus role PJ

        $table->string('judul', 150);
        $table->text('deskripsi');

        $table->enum('status', ['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'])->default('OPEN');
        $table->dateTime('sla_due_at')->nullable();
        $table->dateTime('closed_at')->nullable();

        $table->timestamps();

        $table->index(['kategori','urgensi','status','assignee_id','sla_due_at']);
    });
}
public function down(): void { Schema::dropIfExists('tickets'); }
}
