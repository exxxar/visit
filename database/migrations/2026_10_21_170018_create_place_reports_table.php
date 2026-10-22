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
        Schema::create('place_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('issue_type');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'resolved', 'rejected'])->default('pending');
            $table->timestamps();
        });

// И таблица для вложений (или используй полиморфную связь)
        Schema::create('report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_report_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('type'); // 'image' или 'audio'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_reports');
    }
};
