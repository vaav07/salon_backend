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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');

            // Add user_id column
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('customer_fullname', 60);
            $table->string('email', 40)->unique();
            $table->integer('phone_no');
            $table->integer('alt_phone_no')->nullable();
            $table->string('address')->nullable();
            $table->string('state', 50);
            $table->string('city', 50);
            $table->integer('pincode');
            $table->string('dob', 30);
            $table->enum('gender', ['male', 'female', 'others']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
