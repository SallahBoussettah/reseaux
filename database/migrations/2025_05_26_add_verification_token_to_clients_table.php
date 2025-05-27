<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('verification_token', 6)->nullable();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->integer('verification_token_attempts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('verification_token');
            $table->dropColumn('verification_token_expires_at');
            $table->dropColumn('verification_token_attempts');
        });
    }
}; 