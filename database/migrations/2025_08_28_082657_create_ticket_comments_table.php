<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::create('ticket_comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->text('body');
        $table->boolean('is_internal')->default(false); // internal hanya PJ/Superadmin
        $table->timestamps();
    });
}
public function down(): void { Schema::dropIfExists('ticket_comments'); }
}
