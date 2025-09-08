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
        Schema::create('availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bnb_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->decimal('price_override', 10, 2)->nullable()->comment('Override default price for this date');
            $table->integer('minimum_stay')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Prevent duplicate entries for same BNB and date
            $table->unique(['bnb_id', 'date']);
            $table->index(['bnb_id', 'date', 'is_available']);
            $table->index(['date', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability');
    }
};
