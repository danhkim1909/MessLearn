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
            'title' => 'required|string|max:100',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
        ]);

        try {
            $conversation = DB::transaction(function () use ($request) {
                $conv = Conversation::create([
                    'type' => 'group',
                    'title' => $request->title,
                ]);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => Auth::id(),
                    'role' => 'admin',
                ]);

                if (!empty($request->participant_ids)) {
                    foreach (array_unique($request->participant_ids) as $userId) {
                        if ($userId != Auth::id()) {
                            ConversationParticipant::create([
                                'conversation_id' => $conv->id,
                                'user_id' => $userId,
                                'role' => 'member',
                            ]);
                        }
                    }
                }
                
                return $conv;
            });

            return redirect()->route('app.chat-board.show', $conversation->id)->with('success', 'Đã tạo nhóm chat mới thành công');
        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo nhóm chat '{$request->title}': " . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi tạo nhóm chat');
        }
    }
}
