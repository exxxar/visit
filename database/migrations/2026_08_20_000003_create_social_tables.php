<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('author_name')->nullable();
            $t->unsignedTinyInteger('rating');
            $t->text('text')->nullable();
            $t->string('status', 16)->default('pending')->index(); // ReviewStatus
            $t->timestamps();
        });


    }

    public function down(): void
    {

        Schema::dropIfExists('reviews');
    }
};
