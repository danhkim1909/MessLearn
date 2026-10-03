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
        // 1. Bảng tin nhắn trung tâm (Interactive Messages)
        Schema::create('messages', function (Blueprint $table) {
            $table->id()->comment('Mã định danh tin nhắn');
            
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete()
                ->comment('ID cuộc trò chuyện chứa tin nhắn này');
                
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người gửi tin nhắn');
                
            // Loại tin nhắn:
            // - text: Tin nhắn chữ thông thường
            // - image: Hình ảnh đính kèm (hỗ trợ vẽ lên ảnh)
            // - audio: Tin nhắn ghi âm (voice note)
            // - quiz: Thẻ câu hỏi/đề thi tương tác
            // - game_dice: Trò chơi tung xúc xắc ngẫu nhiên
            // - game_rps: Trò chơi kéo búa bao
            // - event: Lịch hẹn/sự kiện nhóm
            $table->string('type', 32)
                ->default('text')
                ->comment('Loại tin nhắn: text, image, audio, quiz, game_dice, game_rps, event');
                
            // Nội dung tin nhắn dạng văn bản (hoặc caption của ảnh/voice)
            $table->text('body')->nullable()->comment('Nội dung văn bản của tin nhắn');
            
            // Đường dẫn file nếu tin nhắn là ảnh hoặc voice audio
            $table->string('file_path')->nullable()->comment('Đường dẫn file trên server nếu là ảnh hoặc âm thanh');
            
            // Khóa ngoại trỏ đến bài quiz (nếu type = quiz, các loại khác thì null)
            $table->foreignId('quiz_id')
                ->nullable()
                ->constrained('quizzes')
                ->nullOnDelete()
                ->comment('ID bài quiz đính kèm nếu type là quiz');
                
            // Trả lời (Reply / Quote) tin nhắn cũ
            $table->foreignId('reply_to_id')
                ->nullable()
                ->constrained('messages')
                ->nullOnDelete()
                ->comment('ID tin nhắn được trả lời/trích dẫn');
                
            // Trạng thái ghim tin nhắn
            $table->boolean('is_pinned')->default(false)->comment('true nếu tin nhắn đang được ghim');
            
            // Dữ liệu JSON linh hoạt (ví dụ kết quả xúc xắc {"dice": 5}, thông tin sự kiện...)
            $table->json('metadata')->nullable()->comment('Dữ liệu phụ dạng JSON cho các mini-game hoặc event');
            
            $table->timestamps();
        });

        // 2. Bảng thả cảm xúc (Reactions: like, heart, haha, v.v.)
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id()->comment('Mã định danh lượt thả cảm xúc');
            
            $table->foreignId('message_id')
                ->constrained('messages')
                ->cascadeOnDelete()
                ->comment('ID tin nhắn được thả cảm xúc');
                
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người thả cảm xúc');
                
            $table->string('reaction', 20)->comment('Loại cảm xúc: like, heart, laugh, wow, sad, angry');
            $table->timestamps();

            // Mỗi người chỉ thả 1 cảm xúc trên 1 tin nhắn
            $table->unique(['message_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
        Schema::dropIfExists('messages');
    }
};
