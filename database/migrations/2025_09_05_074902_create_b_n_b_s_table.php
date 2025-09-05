<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the bnbs table with all required fields for the BNB Management System.
     * This migration is designed to be portable across SQLite, MySQL, and PostgreSQL.
     */
    public function up(): void
    {
        Schema::create('bnbs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Name of the BNB property');
            $table->string('location')->comment('Location/address of the BNB');
            $table->decimal('price_per_night', 10, 2)->comment('Price per night in decimal format');
            $table->boolean('availability')->default(true)->comment('Availability status of the BNB');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete for data recovery');
            
            // Indexes for better query performance
            $table->index(['availability', 'price_per_night']);
            $table->index('location');
            $table->index('name');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bnbs');
    }
};
