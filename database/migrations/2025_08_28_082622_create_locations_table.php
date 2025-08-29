<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('detail')->nullable(); // mis: Lantai 2, Ruang Produksi A
        $table->timestamps();
    });
}
public function down(): void { Schema::dropIfExists('locations'); }


}
