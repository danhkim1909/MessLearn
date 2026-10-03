<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendshipController extends Controller
{
    public function search(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return response()->json([]);
        }

        $users = User::where('email', 'like', "%{$email}%")
            ->where('id', '!=', Auth::id())
            ->limit(5)
            ->get(['id', 'name', 'email', 'avatar']);

        return response()->json($users);
    }

    public function sendRequest(Request $request)
    {
        $friendId = $request->friend_id;
        $userId = Auth::id();

        if($friendId == $userId) {
            return redirect()->back()->with('error', 'Bạn không thể gửi lời mời kết bạn cho chính mình');
        }

        $friend = User::find($friendId);
        if (!$friend) {
            return redirect()->back()->with('error', 'Người dùng không tồn tại');
        }

        $exists = Friendship::where(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();

        if ($exists) {
            return redirect()->back()->with('error', 'Yêu cầu kết bạn đã tồn tại hoặc hai người đã là bạn');
        }

        Friendship::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Đã gửi lời mời kết bạn thành công');
    }

    public function acceptRequest($id)
    {
        try {
            $friendship = Friendship::where('id', $id)
                ->where('friend_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            $conversation = DB::transaction(function () use ($friendship) {
                $friendship->update(['status' => 'accepted']);

                $conv = Conversation::create([
                    'type' => 'direct',
                ]);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $friendship->user_id,
                    'role' => 'member',
                ]);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $friendship->friend_id,
                    'role' => 'member',
                ]);
                
                return $conv;
            });

            return redirect()->route('app.chat-board.show', $conversation->id)->with('success', 'Đã đồng ý kết bạn và khởi tạo cuộc trò chuyện');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Lỗi khi đồng ý kết bạn (ID: {$id}): " . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi đồng ý kết bạn');
        }
    }
}