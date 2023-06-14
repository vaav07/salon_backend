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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins');

            // Add user_id column
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('fullname', 60);
            $table->string('email', 40)->nullable()->unique();
            $table->string('phone_no', 10)->nullable()->unique();
            // $table->string('alt_phone_no', 10)->nullable();
            // $table->string('address')->nullable();
            // $table->string('state', 50)->nullable();
            // $table->string('city', 50)->nullable();
            // $table->integer('pincode')->nullable();
            // $table->string('dob', 30)->nullable();
            $table->enum('gender', ['male', 'female', 'others'])->nullable();
            $table->string('date_of_joining');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
