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
        // Bảng quan hệ bạn bè giữa người dùng
        Schema::create('friendships', function (Blueprint $table) {
            $table->id()->comment('Mã định danh quan hệ kết bạn');
            
            // Người gửi lời mời kết bạn
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người gửi lời mời kết bạn');
                
            // Người nhận lời mời kết bạn
            $table->foreignId('friend_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID người nhận lời mời kết bạn');
                
            // Trạng thái kết bạn: pending (chờ đồng ý), accepted (đã là bạn bè), blocked (chặn)
            $table->enum('status', ['pending', 'accepted', 'blocked'])
                ->default('pending')
                ->comment('Trạng thái kết bạn: pending, accepted, blocked');
                
            $table->timestamps();

            // Đảm bảo không thể gửi 2 lần lời mời giữa cùng một cặp người dùng
            $table->unique(['user_id', 'friend_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
