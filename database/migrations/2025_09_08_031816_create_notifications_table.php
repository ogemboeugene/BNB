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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'booking_request', 'booking_confirmed', 'message', etc.
            $table->morphs('notifiable'); // User receiving the notification
            $table->text('data'); // JSON data for the notification
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->enum('channel', ['in_app', 'email', 'push', 'sms'])->default('in_app');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'is_read']);
            $table->index(['type', 'created_at']);
            $table->index(['is_read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
