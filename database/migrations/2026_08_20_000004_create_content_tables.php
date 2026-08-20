<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('type', 20)->index(); // EventType
            $t->dateTime('starts_at')->index();
            $t->dateTime('ends_at')->nullable();
            $t->string('image')->nullable();
            $t->string('price', 50)->nullable();
            $t->string('status', 20)->default('draft')->index(); // ModerationStatus
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('news', function (Blueprint $t) {
            $t->id();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('body');
            $t->string('image')->nullable();
            $t->string('status', 20)->default('on_moderation')->index();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('excerpt', 300)->nullable();
            $t->longText('body')->nullable();
            $t->string('cover')->nullable();
            $t->string('tag', 50)->nullable();
            $t->string('status', 16)->default('draft'); // PostStatus
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('place_post', function (Blueprint $t) {
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('place_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('sort')->default(0);
            $t->primary(['post_id', 'place_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_post');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('news');
        Schema::dropIfExists('events');
    }
};
