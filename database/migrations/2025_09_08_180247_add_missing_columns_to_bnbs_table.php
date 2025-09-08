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
            // Check and add missing columns one by one
            if (!Schema::hasColumn('bnbs', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('location');
            }
            if (!Schema::hasColumn('bnbs', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('bnbs', 'description')) {
                $table->text('description')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('bnbs', 'max_guests')) {
                $table->integer('max_guests')->default(1)->after('description');
            }
            if (!Schema::hasColumn('bnbs', 'bedrooms')) {
                $table->integer('bedrooms')->default(1)->after('max_guests');
            }
            if (!Schema::hasColumn('bnbs', 'bathrooms')) {
                $table->integer('bathrooms')->default(1)->after('bedrooms');
            }
            if (!Schema::hasColumn('bnbs', 'amenities')) {
                $table->json('amenities')->nullable()->after('bathrooms');
            }
            if (!Schema::hasColumn('bnbs', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0)->after('amenities');
            }
            if (!Schema::hasColumn('bnbs', 'total_reviews')) {
                $table->integer('total_reviews')->default(0)->after('average_rating');
            }
            if (!Schema::hasColumn('bnbs', 'view_count')) {
                $table->integer('view_count')->default(0)->after('total_reviews');
            }
            if (!Schema::hasColumn('bnbs', 'image_url')) {
                $table->string('image_url')->nullable()->after('view_count');
            }
            if (!Schema::hasColumn('bnbs', 'featured')) {
                $table->boolean('featured')->default(false)->after('availability');
            }
            if (!Schema::hasColumn('bnbs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bnbs', function (Blueprint $table) {
            $columnsToCheck = [
                'latitude', 'longitude', 'description', 'max_guests', 
                'bedrooms', 'bathrooms', 'amenities', 'average_rating', 
                'total_reviews', 'view_count', 'image_url', 'featured'
            ];
            
            $columnsToCheck[] = 'user_id';
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('bnbs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
