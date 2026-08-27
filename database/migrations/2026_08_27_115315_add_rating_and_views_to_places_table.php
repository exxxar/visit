<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0)->after('price_level');
            }

            if (!Schema::hasColumn('places', 'reviews_count')) {
                $table->unsignedInteger('reviews_count')->default(0)->after('rating');
            }

            if (!Schema::hasColumn('places', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('reviews_count');
            }

            // индексы для сортировки
            if (!Schema::hasIndex('places', 'places_rating_index')) {
                $table->index('rating', 'places_rating_index');
            }
            if (!Schema::hasIndex('places', 'places_views_index')) {
                $table->index('views_count', 'places_views_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex('places_rating_index');
            $table->dropIndex('places_views_index');

            $table->dropColumn(['rating', 'reviews_count', 'views_count']);
        });
    }
};
