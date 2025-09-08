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
        Schema::table('bnbs', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->json('amenities')->nullable()->after('longitude');
            $table->text('description')->nullable()->after('amenities');
            $table->integer('max_guests')->default(1)->after('description');
            $table->integer('bedrooms')->default(1)->after('max_guests');
            $table->integer('bathrooms')->default(1)->after('bedrooms');
            $table->decimal('average_rating', 3, 2)->default(0)->after('bathrooms');
            $table->integer('total_reviews')->default(0)->after('average_rating');
            $table->integer('view_count')->default(0)->after('total_reviews');
            
            // Add regular indexes for geolocation queries (SQLite compatible)
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bnbs', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'latitude', 'longitude', 'amenities', 'description',
                'max_guests', 'bedrooms', 'bathrooms', 'average_rating',
                'total_reviews', 'view_count'
            ]);
        });
    }
};
