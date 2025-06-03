<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Keep the existing data_usage column for backward compatibility
            // Add new specific columns for download and upload tracking
            $table->unsignedBigInteger('total_downloaded_bytes')->default(0)->after('data_usage');
            $table->unsignedBigInteger('total_uploaded_bytes')->default(0)->after('total_downloaded_bytes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('total_downloaded_bytes');
            $table->dropColumn('total_uploaded_bytes');
        });
    }
}; 