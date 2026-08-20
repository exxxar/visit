<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('district_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('short_description', 300)->nullable();
            $t->text('description')->nullable();
            $t->string('address');
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('email')->nullable();
            $t->string('site')->nullable();
            $t->json('socials')->nullable();
            $t->json('working_hours')->nullable();
            $t->unsignedTinyInteger('price_level')->default(2);
            $t->string('status', 20)->default('draft')->index(); // ModerationStatus
            $t->boolean('is_featured')->default(false)->index();
            $t->decimal('rating', 3, 2)->default(0);
            $t->unsignedInteger('reviews_count')->default(0);
            $t->unsignedInteger('views_count')->default(0);
            $t->timestamps();
            $t->softDeletes();

            $t->index(['district_id', 'status']);
            $t->index(['category_id', 'status']);
            $t->fullText(['name', 'address', 'short_description']);
        });

        Schema::create('place_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->boolean('is_cover')->default(false);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_photos');
        Schema::dropIfExists('places');
    }
};
