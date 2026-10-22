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
            if (!Schema::hasColumn('users', 'partner_level')) {
                $table->unsignedTinyInteger('partner_level')->default(1);
            }
            if (!Schema::hasColumn('users', 'partner_balance')) {
                $table->decimal('partner_balance', 10, 2)->default(0)->after('partner_level');
            }
            if (!Schema::hasColumn('users', 'completed_orders_count')) {
                $table->unsignedInteger('completed_orders_count')->default(0)->after('partner_balance');
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
