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
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->nullable()->constrained()->onDelete('set null');
            $table->string('floor')->nullable(); // 階数（例：１階）
            $table->string('room_number'); // 部屋番号（例：3号室）
            $table->text('notes')->nullable(); // 備考
            $table->timestamps();
            
            $table->index('plan_id');
            $table->index('participant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_assignments');
    }
};
