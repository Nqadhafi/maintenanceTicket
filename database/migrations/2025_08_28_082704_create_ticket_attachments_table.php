<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('ticket_attachments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
        $table->string('path'); // storage path
        $table->string('mime')->nullable();
        $table->unsignedBigInteger('size')->nullable();
        $table->timestamps();
    });
}
public function down(): void { Schema::dropIfExists('ticket_attachments'); }

}
