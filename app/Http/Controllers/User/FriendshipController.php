<?php

namespace App\Http\Controllers\User;

use App\Events\FriendshipEvent;
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
        $email = trim($request->query('email', ''));
        if (!$email) {
            return response()->json([
                'found' => false,
                'message' => 'Vui lòng nhập địa chỉ email cần tìm kiếm.'
            ], 400);
        }
        
        $targetUser = User::where('email', $email)->first();
        if (!$targetUser) {
            return response()->json([
                'found' => false,
                'message' => 'Không tìm thấy người dùng với email này.'
            ], 404);
        }

        $currentUserId = Auth::id();
        $isSelf = ($targetUser->id === $currentUserId);

        $status = 'none';
        $label = 'Chưa kết bạn';
        $friendshipId = null;
        $conversationId = null;

        if ($isSelf) {
            $status = 'self';
            $label = 'Đây là tài khoản của bạn';
        } else {
            $friendship = Friendship::where(function ($query) use ($currentUserId, $targetUser) {
                $query->where('user_id', $currentUserId)->where('friend_id', $targetUser->id);
            })->orWhere(function ($query) use ($currentUserId, $targetUser) {
                $query->where('user_id', $targetUser->id)->where('friend_id', $currentUserId);
            })->first();

            if ($friendship) {
                $friendshipId = $friendship->id;
                if ($friendship->status === 'accepted') {
                    $status = 'friend';
                    $label = 'Đã là bạn bè';

                    // Tìm cuộc trò chuyện trực tiếp 1-1 giữa hai người nếu có
                    $directConv = Conversation::where('type', 'direct')
                        ->whereHas('participants', function ($q) use ($currentUserId) {
                            $q->where('user_id', $currentUserId);
                        })
                        ->whereHas('participants', function ($q) use ($targetUser) {
                            $q->where('user_id', $targetUser->id);
                        })
                        ->first();
                    $conversationId = $directConv?->id;
                } elseif ($friendship->status === 'pending') {
                    if ($friendship->user_id === $currentUserId) {
                        $status = 'pending_sent';
                        $label = 'Đã gửi lời mời kết bạn (Chờ phản hồi)';
                    } else {
                        $status = 'pending_received';
                        $label = 'Người này đã gửi lời mời cho bạn';
                    }
                } elseif ($friendship->status === 'blocked') {
                    $status = 'blocked';
                    $label = 'Không thể kết bạn với người dùng này';
                }
            }
        }

        return response()->json([
            'found' => true,
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'avatar' => $targetUser->avatar_url,
            ],
            'relationship' => [
                'status' => $status,
                'label' => $label,
                'friendship_id' => $friendshipId,
                'conversation_id' => $conversationId,
            ]
        ]);
    }

    public function sendRequest(Request $request)
    {
        $currentUserId = Auth::id();
        $friendId = $request->input('friend_id');
        $email = trim($request->input('email', ''));

        if (!$friendId && $email) {
            $friend = User::where('email', $email)->first();
            $friendId = $friend?->id;
        } else {
            $friend = User::find($friendId);
        }

        if (!$friend) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
        }

        if ($friend->id === $currentUserId) {
            return response()->json(['message' => 'Bạn không thể gửi lời mời kết bạn cho chính mình.'], 400);
        }

        $exists = Friendship::where(function ($query) use ($currentUserId, $friend) {
            $query->where('user_id', $currentUserId)->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($currentUserId, $friend) {
            $query->where('user_id', $friend->id)->where('friend_id', $currentUserId);
        })->first();

        if ($exists) {
            if ($exists->status === 'accepted') {
                return response()->json(['message' => 'Hai người đã là bạn bè rồi.'], 400);
            }
            if ($exists->status === 'pending') {
                if ($exists->user_id === $currentUserId) {
                    return response()->json(['message' => 'Bạn đã gửi lời mời kết bạn trước đó rồi.'], 400);
                } else {
                    return response()->json(['message' => 'Người này đã gửi lời mời cho bạn, vui lòng chấp nhận lời mời.'], 400);
                }
            }
            if ($exists->status === 'blocked') {
                return response()->json(['message' => 'Không thể gửi lời mời cho người dùng này.'], 400);
            }
        }

        $newFriendship = Friendship::create([
            'user_id' => $currentUserId,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        // Phat su kien Reverb toi nguoi nhan
        broadcast(new FriendshipEvent(
            receiverId: (int)$friend->id,
            action: 'request_sent',
            sender: [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'avatar' => Auth::user()->avatar_url,
                'email' => Auth::user()->email,
            ],
            friendshipId: $newFriendship->id,
            message: Auth::user()->name . ' đã gửi cho bạn một lời mời kết bạn.'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi lời mời kết bạn thành công.',
            'friendship_id' => $newFriendship->id,
        ]);
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

            // Phat su kien Reverb toi nguoi gui loi moi ban dau
            broadcast(new FriendshipEvent(
                receiverId: (int)$friendship->user_id,
                action: 'request_accepted',
                sender: [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'avatar' => Auth::user()->avatar_url,
                    'email' => Auth::user()->email,
                ],
                friendshipId: $friendship->id,
                conversationId: $conversation->id,
                message: Auth::user()->name . ' đã đồng ý lời mời kết bạn của bạn.'
            ))->toOthers();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã đồng ý kết bạn và khởi tạo cuộc trò chuyện.',
                    'conversation_id' => $conversation->id,
                    'redirect_url' => route('app.chat-board.show', $conversation->id),
                ]);
            }

            return redirect()->route('app.chat-board.show', $conversation->id)->with('success', 'Đã đồng ý kết bạn và khởi tạo cuộc trò chuyện.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Lỗi khi đồng ý kết bạn (ID: {$id}): " . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Đã xảy ra lỗi khi đồng ý kết bạn.'], 500);
            }
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi đồng ý kết bạn.');
        }
    }

    public function cancelRequest($id)
    {
        $currentUserId = Auth::id();
        $friendship = Friendship::where('id', $id)
            ->where('user_id', $currentUserId)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Lời mời kết bạn không tồn tại hoặc đã được xử lý.'], 404);
        }

        $friendId = $friendship->friend_id;
        $friendship->delete();

        // Phat su kien Reverb de nguoi nhan xoa loi moi khoi Sidebar theo thoi gian thuc
        broadcast(new FriendshipEvent(
            receiverId: (int)$friendId,
            action: 'request_canceled',
            sender: [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'avatar' => Auth::user()->avatar_url,
                'email' => Auth::user()->email,
            ],
            friendshipId: (int)$id,
            message: 'Lời mời kết bạn đã được rút lại.'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy lời mời kết bạn thành công.'
        ]);
    }

    public function rejectRequest($id)
    {
        $currentUserId = Auth::id();
        $friendship = Friendship::where('id', $id)
            ->where('friend_id', $currentUserId)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Lời mời kết bạn không tồn tại hoặc đã được xử lý.'], 404);
            }
            return redirect()->back()->with('error', 'Lời mời kết bạn không tồn tại.');
        }

        $senderId = $friendship->user_id;
        $friendship->delete();

        // Phat su kien Reverb toi nguoi gui ban dau
        broadcast(new FriendshipEvent(
            receiverId: (int)$senderId,
            action: 'request_rejected',
            sender: [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'avatar' => Auth::user()->avatar_url,
                'email' => Auth::user()->email,
            ],
            friendshipId: (int)$id,
            message: 'Lời mời kết bạn đã bị từ chối.'
        ))->toOthers();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối lời mời kết bạn.'
            ]);
        }

        return redirect()->back()->with('success', 'Đã từ chối lời mời kết bạn.');
    }

    public function unfriend($friendId)
    {
        $currentUserId = Auth::id();
        $friendship = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($currentUserId, $friendId) {
                $q->where('user_id', $currentUserId)->where('friend_id', $friendId);
            })
            ->orWhere(function ($q) use ($currentUserId, $friendId) {
                $q->where('user_id', $friendId)->where('friend_id', $currentUserId);
            })
            ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Quan hệ bạn bè không tồn tại.'], 404);
        }

        $friendship->delete();

        // Phat su kien Reverb toi nguoi bi huy ket ban
        broadcast(new FriendshipEvent(
            receiverId: (int)$friendId,
            action: 'unfriended',
            sender: [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'avatar' => Auth::user()->avatar_url,
                'email' => Auth::user()->email,
            ],
            friendshipId: $friendship->id,
            message: Auth::user()->name . ' đã hủy kết bạn.'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy kết bạn thành công.'
        ]);
    }
}
