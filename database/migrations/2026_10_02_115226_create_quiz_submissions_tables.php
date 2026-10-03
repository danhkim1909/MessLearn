<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('form_id')
                ->constrained('forms')
                ->cascadeOnDelete()
                ->comment('ID form được nộp');
                
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người làm bài');
                
            $table->unsignedInteger('total_score')->default(0)->comment('Tổng số điểm');
            $table->json('answers')->comment('Câu trả lời dạng JSON từ SurveyJS');
            $table->timestamp('completed_at')->nullable()->comment('Thời gian hoàn thành');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
