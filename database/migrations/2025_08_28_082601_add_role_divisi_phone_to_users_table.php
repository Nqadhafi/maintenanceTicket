<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleDivisiPhoneToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('no_wa')->nullable()->after('email');
        $table->enum('role', ['USER','PJ','SUPERADMIN'])->default('USER')->after('password');
        $table->enum('divisi', ['IT','PRODUKSI','GA'])->nullable()->after('role'); // wajib jika PJ
        $table->boolean('aktif')->default(true)->after('divisi');
        $table->index(['role','divisi','aktif']);
    });
}
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['no_wa','role','divisi','aktif']);
    });
}

}
