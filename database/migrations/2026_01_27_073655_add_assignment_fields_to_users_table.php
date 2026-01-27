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
            $table->string('room_day1')->nullable()->after('role');
            $table->string('room_day2')->nullable()->after('room_day1');
            $table->string('room_day3')->nullable()->after('room_day2');
            $table->string('bus_number')->nullable()->after('room_day3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['room_day1', 'room_day2', 'room_day3', 'bus_number']);
        });
    }
};
