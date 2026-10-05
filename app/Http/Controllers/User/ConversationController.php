<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function storeGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:2|max:100',
            'participant_ids' => 'required|array|min:2',
            'participant_ids.*' => 'exists:users,id',
        ], [
            'title.required' => 'Vui lòng nhập tên nhóm học tập.',
            'title.min' => 'Tên nhóm phải có ít nhất 2 ký tự.',
            'title.max' => 'Tên nhóm không được vượt quá 100 ký tự.',
            'participant_ids.required' => 'Vui lòng chọn thành viên cho nhóm.',
            'participant_ids.min' => 'Cần chọn tối thiểu 2 bạn bè để tạo nhóm học tập.',
        ]);

        try {
            $currentUserId = Auth::id();
            
            // Lọc bỏ chính mình và các id trùng lặp
            $rawParticipantIds = $request->input('participant_ids', []);
            $participantIds = array_values(array_unique(array_filter(
                $rawParticipantIds,
                fn ($id) => (int)$id !== (int)$currentUserId
            )));

            if (count($participantIds) < 2) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Vui lòng chọn tối thiểu 2 bạn bè để tạo nhóm học tập.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Vui lòng chọn tối thiểu 2 bạn bè để tạo nhóm học tập.');
            }

            $conversation = DB::transaction(function () use ($request, $currentUserId, $participantIds) {
                $conv = Conversation::create([
                    'type' => 'group',
                    'title' => trim($request->title),
                ]);

                // Người tạo nhóm là admin
                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $currentUserId,
                    'role' => 'admin',
                ]);

                // Các bạn bè được chọn là member
                foreach ($participantIds as $userId) {
                    ConversationParticipant::create([
                        'conversation_id' => $conv->id,
                        'user_id' => $userId,
                        'role' => 'member',
                    ]);
                }
                
                return $conv;
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã tạo nhóm học tập mới thành công.',
                    'conversation_id' => $conversation->id,
                    'redirect_url' => route('app.chat-board.show', $conversation->id),
                ], 201);
            }

            return redirect()->route('app.chat-board.show', $conversation->id)->with('success', 'Đã tạo nhóm chat mới thành công.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo nhóm chat '{$request->title}': " . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Đã xảy ra lỗi khi tạo nhóm chat: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi tạo nhóm chat.');
        }
    }
}
