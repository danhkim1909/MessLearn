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
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id()->comment('Ma dinh danh chan nguoi dung');

            // Nguoi chu dong chan
            $table->foreignId('blocker_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID nguoi thuc hien chan');

            // Nguoi bi chan
            $table->foreignId('blocked_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ID nguoi bi chan');

            $table->timestamps();

            // Mot nguoi chi chan mot nguoi khac duy nhat 1 lan
            $table->unique(['blocker_id', 'blocked_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};
