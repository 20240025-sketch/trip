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
        Schema::create('schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_id')->constrained()->onDelete('cascade');
            $table->time('time')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('transport_type', 50)->nullable();
            $table->string('transport_from')->nullable();
            $table->string('transport_to')->nullable();
            $table->integer('transport_duration')->nullable();
            $table->integer('transport_cost')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['day_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_items');
    }
};
