<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('description');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->json('working_hours')->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'working_hours']);
        });
    }
};
