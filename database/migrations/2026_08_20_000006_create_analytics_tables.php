<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_views', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('ip', 45)->nullable();
            $t->string('source', 40)->nullable();
            $t->timestamp('created_at')->index();
        });

        Schema::create('place_stats', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->unsignedInteger('views')->default(0);
            $t->unsignedInteger('clicks')->default(0);
            $t->unsignedInteger('favorites')->default(0);
            $t->unique(['place_id', 'date']);
        });

        Schema::create('featured_placements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->string('slot', 20)->index(); // PlacementSlot
            $t->date('starts_at');
            $t->date('ends_at');
            $t->decimal('price', 10, 2)->nullable();
            $t->string('status', 16)->default('scheduled'); // PlacementStatus
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->json('value');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('featured_placements');
        Schema::dropIfExists('place_stats');
        Schema::dropIfExists('place_views');
    }
};
