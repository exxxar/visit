<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('org_name');
            $t->string('category');
            $t->string('district')->nullable();
            $t->string('address')->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('email')->nullable();
            $t->string('site')->nullable();
            $t->text('description')->nullable();
            $t->string('contact_name');
            $t->string('contact_position')->nullable();
            $t->string('contact_phone', 20);
            $t->string('contact_email')->nullable();
            $t->json('media')->nullable();
            $t->json('socials')->nullable();
            $t->string('status', 16)->default('new')->index(); // ApplicationStatus
            $t->timestamps();
        });

        Schema::create('moderation_logs', function (Blueprint $t) {
            $t->id();
            $t->morphs('moderatable');
            $t->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 16)->index(); // ModerationAction
            $t->text('comment')->nullable();
            $t->timestamps();
        });

        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('company')->nullable();
            $t->string('position')->nullable();
            $t->string('phone', 20);
            $t->string('email');
            $t->string('interest', 60)->nullable(); // LeadInterest
            $t->boolean('consent_data')->default(false);
            $t->boolean('consent_policy')->default(false);
            $t->boolean('consent_news')->default(false);
            $t->json('utm')->nullable();
            $t->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('token', 64)->unique();
            $t->string('status', 16)->default('active'); // SubscriptionStatus
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('moderation_logs');
        Schema::dropIfExists('applications');
    }
};
