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
        Schema::create('bus_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->nullable()->constrained()->onDelete('set null');
            $table->string('bus_number'); // バス番号（例：1号車）
            $table->string('row_number'); // 列番号（例：2列目）
            $table->enum('side', ['left', 'right']); // 左右（left/right）
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
        Schema::dropIfExists('bus_assignments');
    }
};
