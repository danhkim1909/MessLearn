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
        // 1. Bảng đề thi / Bài tập Quiz (Đã tinh giản: không cần created_by theo yêu cầu)
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id()->comment('Mã định danh bài quiz');
            $table->string('title')->comment('Tiêu đề bài tập hoặc câu hỏi');
            $table->text('description')->nullable()->comment('Mô tả hoặc hướng dẫn làm bài');
            $table->timestamps();
        });

        // 2. Bảng từng câu hỏi trong bài quiz
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id()->comment('Mã định danh câu hỏi');
            
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete()
                ->comment('ID bài quiz chứa câu hỏi này');
                
            $table->text('question_text')->comment('Nội dung câu hỏi');
            
            // Loại câu hỏi:
            // - single_choice: Trắc nghiệm chọn 1 đáp án đúng
            // - multiple_choice: Trắc nghiệm chọn nhiều đáp án
            // - short_answer: Tự luận ngắn điền chữ/số
            $table->enum('type', ['single_choice', 'multiple_choice', 'short_answer'])
                ->default('single_choice')
                ->comment('Loại câu hỏi: single_choice, multiple_choice, short_answer');
                
            $table->unsignedInteger('points')->default(1)->comment('Số điểm của câu hỏi');
            
            // Dành riêng cho câu hỏi tự luận ngắn: đáp án mẫu để máy tự động so sánh chấm điểm
            $table->string('correct_text_answer')->nullable()->comment('Đáp án đúng dạng chuỗi cho câu hỏi tự luận ngắn');
            
            $table->unsignedInteger('order')->default(1)->comment('Thứ tự hiển thị câu hỏi trong đề');
            
            $table->timestamps();
        });

        // 3. Bảng các lựa chọn đáp án (A, B, C, D) cho câu hỏi trắc nghiệm
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id()->comment('Mã định danh lựa chọn đáp án');
            
            $table->foreignId('quiz_question_id')
                ->constrained('quiz_questions')
                ->cascadeOnDelete()
                ->comment('ID câu hỏi chứa lựa chọn này');
                
            $table->text('option_text')->comment('Nội dung đáp án lựa chọn');
            $table->boolean('is_correct')->default(false)->comment('true nếu đây là đáp án đúng, false nếu sai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
