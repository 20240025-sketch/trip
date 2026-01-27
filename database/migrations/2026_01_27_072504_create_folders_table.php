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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_private')->default(false); // 作成者のみに表示
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        
        // プランとフォルダの中間テーブル
        Schema::create('folder_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['folder_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_plan');
        Schema::dropIfExists('folders');
    }
};
