<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email')->nullable();
            $table->integer('wifi_rating')->nullable();
            $table->integer('hotel_rating')->nullable();
            $table->integer('room_rating')->nullable();
            $table->integer('service_rating')->nullable();
            $table->integer('food_rating')->nullable();
            $table->boolean('is_satisfied')->nullable();
            $table->boolean('visit_again')->nullable();
            $table->text('comments')->nullable();
            $table->text('suggestion')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback');
    }
} 