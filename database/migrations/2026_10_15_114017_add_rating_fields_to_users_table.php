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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'partner_rating')) {
                $table->decimal('partner_rating', 3, 2)->default(0)->after('partner_level');
            }
            if (!Schema::hasColumn('users', 'reviews_count')) {
                $table->unsignedInteger('reviews_count')->default(0)->after('partner_rating');
            }
            if (!Schema::hasColumn('users', 'slug')) {
                $table->string('slug', 60)->unique()->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('users', 'portfolio_url')) {
                $table->string('portfolio_url', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
