<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('contact', 150); // телефон или email
            $table->string('subject', 100);
            $table->text('message');
            $table->string('status', 20)->default('new')->index(); // new | in_progress | resolved
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
