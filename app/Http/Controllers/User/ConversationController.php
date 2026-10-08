<?php

namespace App\Http\Controllers\User;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function getOrCreateDirectConversation(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
        ]);

        $currentUserId = Auth::id();
        $targetUserId = (int)$request->input('target_user_id');

        if ($currentUserId === $targetUserId) {
            return response()->json(['message' => 'Không thể tạo cuộc trò chuyện với chính mình.'], 400);
        }

        // Kiem tra chan lien he giua hai ben
        if (Auth::user()->isBlocking($targetUserId) || Auth::user()->isBlockedBy($targetUserId)) {
            return response()->json(['message' => 'Không thể mở cuộc trò chuyện do có chặn liên hệ.'], 403);
        }

        // Tim cuoc tro chuyen 1-1 da ton tai giua hai nguoi
        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })
            ->whereHas('participants', function ($q) use ($targetUserId) {
                $q->where('user_id', $targetUserId);
            })
            ->first();

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($currentUserId, $targetUserId) {
                $conv = Conversation::create([
                    'type' => 'direct',
                ]);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $currentUserId,
                    'role' => 'member',
                ]);

                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $targetUserId,
                    'role' => 'member',
                ]);

                return $conv;
            });
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'redirect_url' => route('app.chat-board.show', $conversation->id),
        ]);
    }

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
            
            // Loc bo chinh minh va cac id trung lap
            $rawParticipantIds = $request->input('participant_ids', []);
            $participantIds = array_values(array_unique(array_filter(
                $rawParticipantIds,
                fn ($id) => (int)$id !== (int)$currentUserId
            )));

            // Loc bo nhung nguoi dung co quan he chan lien he voi nguoi tao nhom
            $blockedUserIds = UserBlock::where('blocker_id', $currentUserId)->pluck('blocked_id')
                ->merge(UserBlock::where('blocked_id', $currentUserId)->pluck('blocker_id'))
                ->toArray();
            $participantIds = array_values(array_diff($participantIds, $blockedUserIds));

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

                // Nguoi tao nhom la admin
                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $currentUserId,
                    'role' => 'admin',
                ]);

                // Cac ban be duoc chon la member
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

    // --- Lay danh sach thanh vien nhom ---
    public function getMembers(Conversation $conversation)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Từ chối truy cập.'], 403);
        }

        $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();

        $members = $conversation->participants()
            ->with('user:id,name,email,avatar')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->user_id,
                    'name' => $p->user?->name ?? 'Người dùng',
                    'email' => $p->user?->email ?? '',
                    'avatar' => $p->user?->avatar_url ?? null,
                    'role' => $p->role,
                    'is_admin' => $p->role === 'admin',
                    'joined_at' => $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong',
                ];
            });

        return response()->json([
            'success' => true,
            'is_current_user_admin' => $isAdmin,
            'current_user_id' => $userId,
            'members' => $members,
        ]);
    }

    // --- Lay danh sach ban be chua co trong nhom de them moi ---
    public function getAvailableFriends(Conversation $conversation)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Từ chối truy cập.'], 403);
        }

        $existingUserIds = $conversation->participants()->pluck('user_id')->toArray();

        // Lay tat ca ban be da accepted
        $friends = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)->orWhere('friend_id', $userId);
            })
            ->with(['sender', 'receiver'])
            ->get()
            ->map(function ($f) use ($userId) {
                return $f->user_id == $userId ? $f->receiver : $f->sender;
            })
            ->filter(function ($u) use ($existingUserIds) {
                return $u && !in_array($u->id, $existingUserIds);
            })
            ->values()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'avatar' => $u->avatar_url,
                ];
            });

        return response()->json([
            'success' => true,
            'friends' => $friends,
        ]);
    }

    // --- Them thanh vien vao nhom hoc tap ---
    public function addMembers(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Từ chối truy cập.'], 403);
        }

        if (!$conversation->is_group) {
            return response()->json(['message' => 'Chức năng chỉ hỗ trợ cho nhóm học tập.'], 400);
        }

        $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();
        if (!$conversation->canMemberInvite() && !$isAdmin) {
            return response()->json(['message' => 'Chỉ Trưởng nhóm mới có quyền thêm thành viên vào nhóm này.'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ], [
            'user_ids.required' => 'Vui lòng chọn ít nhất một thành viên để thêm vào nhóm.',
        ]);

        $existingUserIds = $conversation->participants()->pluck('user_id')->toArray();
        $newIds = array_values(array_unique(array_filter(
            $request->user_ids,
            fn ($id) => !in_array((int)$id, $existingUserIds)
        )));

        // Loc bo nhung nguoi dung co quan he chan lien he
        $blockedUserIds = UserBlock::where('blocker_id', $userId)->pluck('blocked_id')
            ->merge(UserBlock::where('blocked_id', $userId)->pluck('blocker_id'))
            ->toArray();
        $newIds = array_values(array_diff($newIds, $blockedUserIds));

        if (empty($newIds)) {
            return response()->json(['message' => 'Không thể thêm thành viên do đã có trong nhóm hoặc có chặn liên hệ.'], 400);
        }

        foreach ($newIds as $newId) {
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $newId,
                'role' => 'member',
            ]);
        }

        // Tao tin nhan he thong thong bao thanh vien moi
        $newUsers = User::whereIn('id', $newIds)->get();
        $newNames = $newUsers->pluck('name')->implode(', ');
        $sysMsg = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'type' => 'text',
            'body' => Auth::user()->name . " đã thêm {$newNames} vào nhóm học tập.",
            'metadata' => ['is_system' => true]
        ]);

        $sysMsg->load('user:id,name,avatar');
        broadcast(new MessageSent($sysMsg))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm thành viên vào nhóm học tập thành công.',
            'added_count' => count($newIds),
            'system_message' => $sysMsg,
        ]);
    }

    // --- Truong nhom moi thanh vien roi khoi nhom ---
    public function removeMember(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();

        if (!$isAdmin) {
            return response()->json(['message' => 'Chỉ Trưởng nhóm mới có quyền mời thành viên rời nhóm.'], 403);
        }

        if (!$conversation->is_group) {
            return response()->json(['message' => 'Chức năng chỉ hỗ trợ cho nhóm học tập.'], 400);
        }

        $targetUserId = (int)$request->input('user_id');
        if ($targetUserId === (int)$userId) {
            return response()->json(['message' => 'Bạn không thể tự xóa chính mình bằng chức năng này. Hãy chọn Rời nhóm.'], 400);
        }

        $targetParticipant = $conversation->participants()->where('user_id', $targetUserId)->first();
        if (!$targetParticipant) {
            return response()->json(['message' => 'Thành viên không tồn tại trong nhóm này.'], 404);
        }

        $targetUser = User::find($targetUserId);
        $targetParticipant->delete();

        // Tao tin nhan he thong
        $targetName = $targetUser?->name ?? 'Một thành viên';
        $sysMsg = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'type' => 'text',
            'body' => Auth::user()->name . " đã mời {$targetName} rời khỏi nhóm học tập.",
            'metadata' => ['is_system' => true]
        ]);

        $sysMsg->load('user:id,name,avatar');
        broadcast(new MessageSent($sysMsg))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "Đã mời {$targetName} rời khỏi nhóm học tập.",
            'system_message' => $sysMsg,
        ]);
    }

    // --- Tu roi khoi nhom hoc tap ---
    public function leaveGroup(Conversation $conversation)
    {
        $userId = Auth::id();
        $participant = $conversation->participants()->where('user_id', $userId)->first();

        if (!$participant) {
            return response()->json(['message' => 'Bạn không thuộc nhóm này.'], 404);
        }

        if (!$conversation->is_group) {
            return response()->json(['message' => 'Chức năng chỉ hỗ trợ cho nhóm học tập.'], 400);
        }

        // Neu la admin, chuyen giao quyen admin cho thanh vien khac neu con nguoi
        $promotedAdminName = null;
        if ($participant->role === 'admin') {
            $nextMember = $conversation->participants()
                ->where('user_id', '!=', $userId)
                ->with('user')
                ->first();
            if ($nextMember) {
                $nextMember->update(['role' => 'admin']);
                $promotedAdminName = $nextMember->user?->name;
            }
        }

        $participant->delete();

        // Kiem tra so thanh vien con lai trong nhom
        $remainingCount = $conversation->participants()->count();
        if ($remainingCount === 0) {
            $conversation->delete();
            return response()->json([
                'success' => true,
                'message' => 'Bạn đã rời nhóm. Nhóm đã tự động giải tán do không còn thành viên.',
                'redirect_url' => route('app.chat-board'),
            ]);
        }

        // Tao tin nhan he thong
        $sysBody = Auth::user()->name . " đã rời khỏi nhóm học tập.";
        if ($promotedAdminName) {
            $sysBody .= " Quyền Trưởng nhóm được chuyển giao cho {$promotedAdminName}.";
        }

        $sysMsg = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'type' => 'text',
            'body' => $sysBody,
            'metadata' => ['is_system' => true]
        ]);

        $sysMsg->load('user:id,name,avatar');
        broadcast(new MessageSent($sysMsg))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Bạn đã rời khỏi nhóm học tập.',
            'redirect_url' => route('app.chat-board'),
        ]);
    }

    // --- Lay thong tin & cai dat nhom hoc tap ---
    public function getGroupInfo(Conversation $conversation)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Từ chối truy cập.'], 403);
        }

        if (!$conversation->is_group) {
            return response()->json(['message' => 'Chức năng chỉ hỗ trợ cho nhóm học tập.'], 400);
        }

        $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();

        return response()->json([
            'success' => true,
            'group' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'avatar' => $conversation->avatar_url,
                'description' => $conversation->description ?? '',
                'settings' => [
                    'read_only' => $conversation->isReadOnly(),
                    'allow_member_start_call' => $conversation->canMemberStartCall(),
                    'allow_member_invite' => $conversation->canMemberInvite(),
                ],
                'is_admin' => $isAdmin,
                'member_count' => $conversation->participants()->count(),
                'created_at' => $conversation->created_at ? $conversation->created_at->format('d/m/Y') : '',
            ]
        ]);
    }

    // --- Cap nhat thong tin nhom hoc tap (Ten, mo ta, avatar) ---
    public function updateGroupInfo(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();

        if (!$isAdmin) {
            return response()->json(['message' => 'Chỉ Trưởng nhóm mới có quyền chỉnh sửa thông tin nhóm.'], 403);
        }

        if (!$conversation->is_group) {
            return response()->json(['message' => 'Chức năng chỉ hỗ trợ cho nhóm học tập.'], 400);
        }

        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ], [
            'title.required' => 'Vui lòng nhập tên nhóm.',
            'title.max' => 'Tên nhóm không được vượt quá 100 ký tự.',
            'description.max' => 'Mô tả nhóm không được vượt quá 1000 ký tự.',
            'avatar.image' => 'Ảnh đại diện nhóm không hợp lệ.',
            'avatar.max' => 'Dung lượng ảnh tối đa 2MB.',
        ]);

        $oldTitle = $conversation->title;
        $conversation->title = $request->input('title');
        $conversation->description = $request->input('description');

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars/groups', 'public');
            $conversation->avatar = $path;
        }

        $conversation->save();

        // Tao tin nhan he thong
        $sysMsgText = Auth::user()->name . " đã cập nhật thông tin nhóm học tập.";
        if ($oldTitle !== $conversation->title) {
            $sysMsgText = Auth::user()->name . " đã đổi tên nhóm thành \"{$conversation->title}\".";
        }

        $sysMsg = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'type' => 'text',
            'body' => $sysMsgText,
            'metadata' => ['is_system' => true]
        ]);

        $sysMsg->load('user:id,name,avatar');
        broadcast(new MessageSent($sysMsg))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thông tin nhóm thành công.',
            'group' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'avatar' => $conversation->avatar_url,
                'description' => $conversation->description,
            ],
            'system_message' => $sysMsg,
        ]);
    }

    // --- Cap nhat cai dat quyen han nhom hoc tap ---
    public function updateGroupSettings(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();

        if (!$isAdmin) {
            return response()->json(['message' => 'Chỉ Trưởng nhóm mới có quyền cài đặt quyền hạn nhóm.'], 403);
        }

        if (!$conversation->is_group) {
            return response()->json(['message' => 'Chức năng chỉ hỗ trợ cho nhóm học tập.'], 400);
        }

        $request->validate([
            'read_only' => 'required|boolean',
            'allow_member_start_call' => 'required|boolean',
            'allow_member_invite' => 'required|boolean',
        ]);

        $oldReadOnly = $conversation->isReadOnly();
        $newReadOnly = (bool)$request->input('read_only');

        $conversation->settings = [
            'read_only' => $newReadOnly,
            'allow_member_start_call' => (bool)$request->input('allow_member_start_call'),
            'allow_member_invite' => (bool)$request->input('allow_member_invite'),
        ];
        $conversation->save();

        // Neu thay doi che do chi doc thi phat thong bao he thong
        if ($oldReadOnly !== $newReadOnly) {
            $body = $newReadOnly
                ? Auth::user()->name . " đã bật chế độ Chỉ đọc (Chỉ Trưởng nhóm mới có thể gửi tin nhắn)."
                : Auth::user()->name . " đã tắt chế độ Chỉ đọc (Tất cả thành viên đều có thể gửi tin nhắn).";

            $sysMsg = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'type' => 'text',
                'body' => $body,
                'metadata' => ['is_system' => true]
            ]);

            $sysMsg->load('user:id,name,avatar');
            broadcast(new MessageSent($sysMsg))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật cài đặt nhóm thành công.',
            'settings' => $conversation->settings,
        ]);
    }

    // --- Ghim / Bo ghim cuoc tro chuyen tren sidebar ---
    public function togglePin(Conversation $conversation)
    {
        $userId = Auth::id();
        $participant = $conversation->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        $participant->is_pinned = !$participant->is_pinned;
        $participant->save();

        return response()->json([
            'success' => true,
            'is_pinned' => $participant->is_pinned,
            'message' => $participant->is_pinned ? 'Đã ghim cuộc trò chuyện lên đầu.' : 'Đã bỏ ghim cuộc trò chuyện.',
        ]);
    }

    // --- Bat / Tat thong bao cuoc tro chuyen (Mute) ---
    public function updateMute(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $participant = $conversation->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        $request->validate([
            'duration' => 'required|in:1h,8h,forever,unmute',
        ]);

        $duration = $request->input('duration');
        if ($duration === 'unmute') {
            $participant->muted_until = null;
            $message = 'Đã bật lại thông báo cuộc trò chuyện.';
        } elseif ($duration === '1h') {
            $participant->muted_until = now()->addHour();
            $message = 'Đã tắt thông báo trong 1 giờ.';
        } elseif ($duration === '8h') {
            $participant->muted_until = now()->addHours(8);
            $message = 'Đã tắt thông báo trong 8 giờ.';
        } else {
            $participant->muted_until = now()->addYears(50);
            $message = 'Đã tắt thông báo cho đến khi bạn bật lại.';
        }

        $participant->save();

        return response()->json([
            'success' => true,
            'is_muted' => $participant->isMuted(),
            'muted_until' => $participant->muted_until ? $participant->muted_until->toIso8601String() : null,
            'message' => $message,
        ]);
    }

    // --- Cap nhat biet danh cuoc tro chuyen ---
    public function updateNickname(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $participant = $conversation->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        $request->validate([
            'nickname' => 'nullable|string|max:100',
        ]);

        $participant->nickname = $request->input('nickname');
        $participant->save();

        return response()->json([
            'success' => true,
            'nickname' => $participant->nickname,
            'message' => 'Đã cập nhật biệt danh cuộc trò chuyện thành công.',
        ]);
    }

    // --- Danh dau tin nhan da xem (Mark as Read) ---
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if (!$participant) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        $messageId = $request->input('message_id');
        if (!$messageId) {
            $messageId = $conversation->messages()->latest('id')->value('id');
        }

        if ($messageId) {
            $messageId = (int)$messageId;
            $currentLastRead = (int)($participant->last_read_message_id ?? 0);

            if ($messageId > $currentLastRead) {
                $participant->last_read_message_id = $messageId;
                $participant->last_read_at = now();
                $participant->save();

                broadcast(new \App\Events\MessageRead(
                    $conversation->id,
                    $user->id,
                    $user->name,
                    $user->avatar_url,
                    $messageId,
                    $participant->last_read_at->toIso8601String()
                ))->toOthers();
            }
        }

        return response()->json([
            'success' => true,
            'last_read_message_id' => $participant->last_read_message_id,
            'last_read_at' => $participant->last_read_at ? $participant->last_read_at->toIso8601String() : null,
        ]);
    }
}

