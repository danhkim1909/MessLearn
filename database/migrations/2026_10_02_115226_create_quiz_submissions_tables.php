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
        // 1. Bảng lượt nộp bài quiz của học sinh/sinh viên
        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->id()->comment('Mã định danh lượt nộp bài');
            
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete()
                ->comment('ID bài quiz được làm');
                
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người làm bài');
                
            // Tổng điểm đạt được sau khi hệ thống tự động chấm
            $table->unsignedInteger('total_score')->default(0)->comment('Tổng số điểm người dùng đạt được');
            
            // Thời gian nộp bài hoàn tất
            $table->timestamp('completed_at')->nullable()->comment('Thời gian hoàn thành bài làm');
            
            $table->timestamps();
        });

        // 2. Bảng chi tiết từng câu trả lời trong đợt nộp bài
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id()->comment('Mã định danh câu trả lời chi tiết');
            
            $table->foreignId('quiz_submission_id')
                ->constrained('quiz_submissions')
                ->cascadeOnDelete()
                ->comment('ID lượt nộp bài');
                
            $table->foreignId('quiz_question_id')
                ->constrained('quiz_questions')
                ->cascadeOnDelete()
                ->comment('ID câu hỏi được trả lời');
                
            // Dành cho trắc nghiệm: ID lựa chọn được chọn (A, B, C, D)
            $table->foreignId('selected_option_id')
                ->nullable()
                ->constrained('quiz_options')
                ->nullOnDelete()
                ->comment('ID đáp án trắc nghiệm đã chọn');
                
            // Dành cho tự luận ngắn: chữ mà người làm tự gõ vào
            $table->string('text_answer')->nullable()->comment('Câu trả lời dạng văn bản nếu là câu tự luận ngắn');
            
            // Hệ thống tự động đánh giá đúng hay sai
            $table->boolean('is_correct')->default(false)->comment('true nếu trả lời đúng, false nếu sai');
            
            // Số điểm đạt được cho riêng câu này
            $table->unsignedInteger('points_earned')->default(0)->comment('Số điểm đạt được cho câu này');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_submissions');
    }
};
