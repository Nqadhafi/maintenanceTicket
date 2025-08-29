<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('asset_categories', function (Blueprint $table) {
        $table->id();
        $table->string('nama')->unique(); // IT | PRODUKSI | GA
        $table->string('deskripsi')->nullable();
        $table->timestamps();
    });
}
public function down(): void { Schema::dropIfExists('asset_categories'); }
}
