<?php

namespace App\Http\Controllers\User;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        try {
            $request->validate([
                'body' => 'required|string|max:2000',
                'reply_to_id' => 'nullable|exists:messages,id',
            ]);

            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                Log::warning("Người dùng {$userId} cố gắng gửi tin nhắn vào cuộc trò chuyện {$conversation->id} mà không có quyền.");
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'type' => 'text',
                'body' => $request->body,
                'reply_to_id' => $request->reply_to_id,
            ]);

            $message->load(['user', 'replyTo.user']);

            $conversation->touch();

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Lỗi khi gửi tin nhắn trong cuộc trò chuyện {$conversation->id}: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi máy chủ'], 500);
        }
    }
}
