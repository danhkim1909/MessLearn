<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Tiêu đề form');
            $table->text('description')->nullable()->comment('Mô tả form');
            $table->string('type', 30)->default('quiz')->comment('Loại: quiz hoặc survey');
            $table->json('schema')->comment('Cấu trúc câu hỏi JSON của SurveyJS');
            $table->json('settings')->nullable()->comment('Cài đặt thêm JSON (time_limit, etc)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
