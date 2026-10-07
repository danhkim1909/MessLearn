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
        // 1. Bang cuoc goi va phong hop truc tuyen
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('room_code', 32)->unique();
            $table->enum('type', ['voice', 'video'])->default('video');
            $table->enum('status', ['ringing', 'ongoing', 'ended'])->default('ringing');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // 2. Bang thanh vien tham gia cuoc hop
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['ringing', 'joined', 'left', 'rejected'])->default('ringing');
            $table->boolean('is_muted_audio')->default(false);
            $table->boolean('is_muted_video')->default(false);
            $table->boolean('is_sharing_screen')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
    }
};
