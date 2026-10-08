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
        // 1. Bảng cuộc trò chuyện / phòng chat
        Schema::create('conversations', function (Blueprint $table) {
            $table->id()->comment('Mã định danh cuộc trò chuyện');
            
            // Loại phòng: direct (chat đôi 1-1), group (chat nhóm nhiều người)
            $table->enum('type', ['direct', 'group'])
                ->default('direct')
                ->comment('Loại cuộc trò chuyện: direct hoặc group');
                
            // Tên nhóm (chỉ bắt buộc khi là group chat, chat 1-1 có thể null)
            $table->string('title')->nullable()->comment('Tên phòng chat nếu là nhóm');
            
            // Ảnh đại diện của phòng chat (nếu là nhóm)
            $table->string('avatar')->nullable()->comment('Ảnh đại diện nhóm');
            
            // Mô tả mục tiêu hoạt động của nhóm
            $table->text('description')->nullable()->comment('Mô tả mục tiêu hoạt động của nhóm');

            // Cài đặt quyền hạn phòng chat (chỉ đọc, ai được gọi, ai được mời)
            $table->json('settings')->nullable()->comment('Cài đặt quyền hạn phòng chat');
            
            $table->timestamps();
        });

        // 2. Bảng thành viên tham gia phòng chat
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id()->comment('Mã định danh thành viên trong phòng chat');
            
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete()
                ->comment('ID cuộc trò chuyện');
                
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người dùng tham gia');
                
            // Vai trò trong phòng chat: admin (quản trị viên nhóm), member (thành viên thường)
            $table->enum('role', ['admin', 'member'])
                ->default('member')
                ->comment('Vai trò: admin hoặc member');
                
            // Đánh dấu thời điểm đọc tin cuối để tính số tin nhắn chưa đọc
            $table->timestamp('last_read_at')->nullable()->comment('Thời gian xem tin nhắn gần nhất');
            
            // Ghim cuộc trò chuyện lên đầu danh sách
            $table->boolean('is_pinned')->default(false)->comment('Ghim cuộc trò chuyện lên đầu danh sách');

            // Biệt danh riêng trong cuộc trò chuyện
            $table->string('nickname', 100)->nullable()->comment('Biệt danh riêng trong cuộc trò chuyện');

            // Thời điểm hết hạn tắt thông báo
            $table->timestamp('muted_until')->nullable()->comment('Thời điểm hết hạn tắt thông báo');

            // ID tin nhắn đã đọc gần nhất
            $table->unsignedBigInteger('last_read_message_id')->nullable()->comment('ID tin nhắn đã đọc gần nhất');

            $table->timestamps();

            // Mỗi người dùng chỉ xuất hiện 1 lần trong 1 cuộc trò chuyện
            $table->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
