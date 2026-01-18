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
        Schema::table('room_assignments', function (Blueprint $table) {
            $table->foreignId('day_id')->nullable()->after('plan_id')->constrained()->onDelete('cascade');
            $table->index('day_id');
        });

        Schema::table('bus_assignments', function (Blueprint $table) {
            $table->foreignId('day_id')->nullable()->after('plan_id')->constrained()->onDelete('cascade');
            $table->index('day_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_assignments', function (Blueprint $table) {
            $table->dropForeign(['day_id']);
            $table->dropColumn('day_id');
        });

        Schema::table('bus_assignments', function (Blueprint $table) {
            $table->dropForeign(['day_id']);
            $table->dropColumn('day_id');
        });
    }
};
