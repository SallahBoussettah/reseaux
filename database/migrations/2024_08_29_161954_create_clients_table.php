<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('remember_token', 250)->nullable();
            $table->string('mac_address', 250);
            $table->string('language', 250)->nullable();
            $table->string('device_type', 250)->nullable();
            $table->string('platform', 250)->nullable();
            $table->string('browser', 250)->nullable();
            $table->date('premium_expires_at')->nullable();
            $table->enum('status', ['active', 'deactivated'])->default('active');
            $table->timestamp('last_login_at')->nullable()->useCurrent();
            $table->bigInteger('data_usage')->default(0);
            $table->integer('login_count')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('verification_token', 6)->nullable();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->integer('verification_token_attempts')->default(0);
            $table->integer('successful_verifications')->nullable()->default(0);
            $table->string('profile_type', 191)->default('free_user');
            $table->timestamp('scheduled_deletion_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}