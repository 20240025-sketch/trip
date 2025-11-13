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
        Schema::create('plan_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('original_name'); // 元のファイル名
            $table->string('stored_name'); // ストレージに保存されたファイル名
            $table->string('file_path'); // ファイルパス
            $table->string('mime_type'); // MIMEタイプ
            $table->integer('file_size'); // ファイルサイズ（バイト）
            $table->integer('order')->default(0); // 表示順序
            $table->timestamps();
            
            $table->index('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_attachments');
    }
};
