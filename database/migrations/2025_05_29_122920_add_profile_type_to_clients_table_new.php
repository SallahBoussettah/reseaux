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
            if (!Schema::hasColumn('clients', 'profile_type')) {
                $table->string('profile_type')->nullable();
            }
            
            if (!Schema::hasColumn('clients', 'scheduled_deletion_at')) {
                $table->timestamp('scheduled_deletion_at')->nullable();
            }
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
            if (Schema::hasColumn('clients', 'profile_type')) {
                $table->dropColumn('profile_type');
            }
            
            if (Schema::hasColumn('clients', 'scheduled_deletion_at')) {
                $table->dropColumn('scheduled_deletion_at');
            }
        });
    }
};
