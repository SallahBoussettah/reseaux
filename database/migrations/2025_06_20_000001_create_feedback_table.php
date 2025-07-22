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
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->integer('wifi_rating')->nullable(); // Rating from 1-5
            $table->integer('hotel_rating')->nullable(); // Rating from 1-5
            $table->integer('room_rating')->nullable(); // Rating from 1-5 
            $table->integer('service_rating')->nullable(); // Rating from 1-5
            $table->integer('food_rating')->nullable(); // Rating from 1-5
            $table->text('comments')->nullable(); // General comments
            $table->text('suggestion')->nullable(); // Suggestions for improvement
            $table->boolean('is_satisfied')->nullable(); // Overall satisfaction
            $table->boolean('visit_again')->nullable(); // Would visit again
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