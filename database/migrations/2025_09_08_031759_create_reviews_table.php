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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bnb_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->text('comment')->nullable();
            $table->json('feedback_categories')->nullable()->comment('Cleanliness, Communication, etc.');
            $table->boolean('is_verified')->default(false)->comment('Verified stay');
            $table->timestamp('stay_date')->nullable();
            $table->timestamps();
            
            // Ensure user can only review each BNB once
            $table->unique(['user_id', 'bnb_id']);
            $table->index(['bnb_id', 'rating']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
