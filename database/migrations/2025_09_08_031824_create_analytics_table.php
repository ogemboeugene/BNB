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
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // 'view', 'booking_request', 'booking_confirmed', etc.
            $table->morphs('trackable'); // BNB, User, etc.
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('properties')->nullable(); // Additional event data
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->decimal('revenue', 10, 2)->nullable(); // For booking events
            $table->timestamps();
            
            $table->index(['event_type', 'created_at']);
            $table->index(['trackable_type', 'trackable_id', 'event_type']);
            $table->index(['user_id', 'event_type']);
            $table->index(['created_at']); // For time-based queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
