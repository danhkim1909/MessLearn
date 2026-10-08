<?php

namespace App\Http\Controllers\User;

use App\Events\CallSignalEvent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Message;
use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    // --- Khoi tao cuoc goi / cuoc hop ---
    public function start(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();

        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Ban khong co quyen tham gia cuoc tro chuyen nay.'], 403);
        }

        if (!$conversation->is_group) {
            $otherParticipant = $conversation->participants()->where('user_id', '!=', $userId)->first();
            if ($otherParticipant) {
                $otherUserId = $otherParticipant->user_id;
                $isBlocked = UserBlock::where(function($q) use ($userId, $otherUserId) {
                    $q->where('blocker_id', $userId)->where('blocked_id', $otherUserId);
                })->orWhere(function($q) use ($userId, $otherUserId) {
                    $q->where('blocker_id', $otherUserId)->where('blocked_id', $userId);
                })->exists();

                if ($isBlocked) {
                    return response()->json(['message' => 'Không thể thực hiện cuộc gọi do có chặn liên hệ.'], 403);
                }
            }
        } else {
            // Kiem tra quyen bat dau cuoc goi trong nhom
            if (!$conversation->canMemberStartCall()) {
                $isAdmin = $conversation->participants()->where('user_id', $userId)->where('role', 'admin')->exists();
                if (!$isAdmin) {
                    return response()->json(['message' => 'Chỉ Trưởng nhóm mới có quyền bắt đầu cuộc gọi trong nhóm này.'], 403);
                }
            }
        }

        $type = $request->input('type', 'video');
        if (!in_array($type, ['voice', 'video'])) {
            $type = 'video';
        }

        $mode = $request->input('mode', 'call');
        if (!in_array($mode, ['call', 'classroom'])) {
            $mode = 'call';
        }

        // Kiem tra xem da co cuoc hop nao dang dien ra trong phong khong
        $activeMeeting = $conversation->meetings()
            ->whereIn('status', ['ringing', 'ongoing'])
            ->latest('id')
            ->first();

        if ($activeMeeting) {
            $participant = MeetingParticipant::firstOrCreate(
                ['meeting_id' => $activeMeeting->id, 'user_id' => $userId],
                ['status' => 'joined', 'joined_at' => now()]
            );

            if ($participant->status !== 'joined') {
                $participant->update([
                    'status' => 'joined',
                    'joined_at' => now(),
                    'left_at' => null,
                ]);
            }

            // Lay danh sach cac thanh vien dang co mat trong phong (ngoai tru ban than)
            $existingParticipants = MeetingParticipant::where('meeting_id', $activeMeeting->id)
                ->where('status', 'joined')
                ->where('user_id', '!=', $userId)
                ->with('user')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->user_id,
                    'name' => $p->user?->name ?? 'Bạn học',
                    'avatar' => $p->user?->avatar_url ?? '',
                ])
                ->values()
                ->toArray();

            $existingParticipantUserIds = array_column($existingParticipants, 'id');

            // Phat tin hieu cho cac thanh vien cu biet co nguoi moi vao phong
            broadcast(new CallSignalEvent(
                conversationId: $conversation->id,
                action: 'participant_joined',
                senderId: $userId,
                senderName: Auth::user()->name,
                senderAvatar: Auth::user()->avatar_url,
                targetUserId: null,
                roomCode: $activeMeeting->room_code,
                callType: $activeMeeting->type,
                payload: [
                    'user_id' => $userId,
                    'user_name' => Auth::user()->name,
                    'user_avatar' => Auth::user()->avatar_url,
                ],
                targetUserIds: $existingParticipantUserIds
            ))->toOthers();

            return response()->json([
                'success' => true,
                'meeting' => $activeMeeting,
                'room_code' => $activeMeeting->room_code,
                'is_existing' => true,
                'is_host' => $activeMeeting->host_id === $userId,
                'is_group' => (bool)$conversation->is_group,
                'mode' => $mode,
                'host_id' => $activeMeeting->host_id,
                'host_name' => $activeMeeting->host?->name ?? 'Chủ phòng',
                'existing_participants' => $existingParticipants,
            ]);
        }

        $roomCode = 'mln-' . Str::lower(Str::random(9));

        $meeting = DB::transaction(function () use ($conversation, $userId, $roomCode, $type) {
            $m = Meeting::create([
                'conversation_id' => $conversation->id,
                'host_id' => $userId,
                'room_code' => $roomCode,
                'type' => $type,
                'status' => 'ringing',
                'started_at' => now(),
            ]);

            MeetingParticipant::create([
                'meeting_id' => $m->id,
                'user_id' => $userId,
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            return $m;
        });

        $targetUserIds = $conversation->participants()
            ->where('user_id', '!=', $userId)
            ->pluck('user_id')
            ->toArray();
        $targetUserId = count($targetUserIds) === 1 ? (int)$targetUserIds[0] : null;

        // Phan biet: Phong hoc nhom (classroom) phat banner thong bao, con cuoc goi thoai/video (call) hoac 1-1 thi do chuong
        $callAction = ($conversation->is_group && $mode === 'classroom') ? 'meeting_started_banner' : 'incoming_call';

        // Phat su kien cuoc goi den qua Reverb
        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: $callAction,
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar_url,
            targetUserId: $targetUserId,
            roomCode: $roomCode,
            callType: $type,
            payload: [
                'meeting_id' => $meeting->id,
                'is_group' => (bool)$conversation->is_group,
                'mode' => $mode,
                'title' => $conversation->name,
                'host_id' => $meeting->host_id,
                'host_name' => Auth::user()->name,
            ],
            targetUserIds: $targetUserIds
        ))->toOthers();

        return response()->json([
            'success' => true,
            'meeting' => $meeting,
            'room_code' => $roomCode,
            'is_existing' => false,
            'is_host' => true,
            'is_group' => (bool)$conversation->is_group,
            'mode' => $mode,
            'host_id' => $meeting->host_id,
            'host_name' => Auth::user()->name,
            'existing_participants' => [],
        ], 201);
    }

    // --- Chuyen tiep tin hieu WebRTC (SDP / ICE / Media State) ---
    public function signal(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();

        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Tu choi truy cap.'], 403);
        }

        $action = $request->input('action');
        $targetUserId = $request->input('target_user_id');
        $roomCode = $request->input('room_code');
        $callType = $request->input('call_type', 'video');
        $payload = $request->input('payload', []);

        // Neu chap nhan cuoc goi, cap nhat trang thai meeting thanh ongoing
        if ($action === 'accept_call' && $roomCode) {
            $meeting = Meeting::where('room_code', $roomCode)->first();
            if ($meeting) {
                if ($meeting->status === 'ringing') {
                    $meeting->update(['status' => 'ongoing']);
                }
                MeetingParticipant::updateOrCreate(
                    ['meeting_id' => $meeting->id, 'user_id' => $userId],
                    ['status' => 'joined', 'joined_at' => now()]
                );
            }
        }

        // Phat tin hieu cho client khac
        $targetUserIds = $conversation->participants()
            ->where('user_id', '!=', $userId)
            ->pluck('user_id')
            ->toArray();

        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: $action,
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar_url,
            targetUserId: $targetUserId ? (int)$targetUserId : null,
            roomCode: $roomCode,
            callType: $callType,
            payload: $payload,
            targetUserIds: $targetUserIds
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    // --- Tu choi cuoc goi ---
    public function reject(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $roomCode = $request->input('room_code');

        if ($roomCode) {
            $meeting = Meeting::where('room_code', $roomCode)->first();
            if ($meeting && !$conversation->is_group) {
                $meeting->update([
                    'status' => 'ended',
                    'ended_at' => now(),
                ]);

                // Ghi nhat ky cuoc goi nho
                Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $meeting->host_id,
                    'type' => 'text',
                    'body' => 'Cuộc gọi nhỡ',
                    'metadata' => [
                        'call_status' => 'missed',
                        'type' => $meeting->type,
                    ],
                ]);
            }
        }

        $targetUserIds = $conversation->participants()
            ->where('user_id', '!=', $userId)
            ->pluck('user_id')
            ->toArray();
        $targetUserId = count($targetUserIds) === 1 ? (int)$targetUserIds[0] : null;

        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: 'reject_call',
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar_url,
            targetUserId: $targetUserId,
            roomCode: $roomCode,
            callType: $request->input('call_type', 'video'),
            targetUserIds: $targetUserIds
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    // --- Roi phong / Ket thuc cuoc goi ---
    public function leave(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $roomCode = $request->input('room_code');
        $endForAll = $request->boolean('end_for_all');
        $isMeetingEnded = false;
        $newHostId = null;
        $remainingParticipants = 0;

        if ($roomCode) {
            $meeting = Meeting::where('room_code', $roomCode)->first();
            if ($meeting) {
                MeetingParticipant::where('meeting_id', $meeting->id)
                    ->where('user_id', $userId)
                    ->update(['status' => 'left', 'left_at' => now()]);

                // Kiem tra so thanh vien con lai trong phong
                $remainingParticipants = MeetingParticipant::where('meeting_id', $meeting->id)
                    ->where('status', 'joined')
                    ->count();

                // Neu la 1-1 hoac khong con ai hoac chu phong chon ket thuc cho tat ca
                if (!$conversation->is_group || $remainingParticipants <= 0 || ($endForAll && $meeting->host_id === $userId)) {
                    if ($meeting->status !== 'ended') {
                        $meeting->update([
                            'status' => 'ended',
                            'ended_at' => now(),
                        ]);

                        $durationStr = $meeting->duration_formatted;
                        $typeName = $conversation->is_group 
                            ? 'Phòng học nhóm' 
                            : ($meeting->type === 'voice' ? 'Cuộc gọi thoại' : 'Cuộc gọi video');

                        Message::create([
                            'conversation_id' => $conversation->id,
                            'user_id' => $meeting->host_id,
                            'type' => 'text',
                            'body' => "{$typeName} đã kết thúc. Thời lượng: {$durationStr}",
                            'metadata' => [
                                'call_status' => 'ended',
                                'duration_seconds' => $meeting->duration_seconds,
                                'duration_formatted' => $durationStr,
                                'type' => $meeting->type,
                                'is_group' => (bool)$conversation->is_group,
                            ],
                        ]);
                        $isMeetingEnded = true;
                    }
                } else {
                    // Truong hop phong nhom van con nguoi: neu Host roi phong thi chuyen giao quyen Host
                    if ($meeting->host_id === $userId) {
                        $nextHost = MeetingParticipant::where('meeting_id', $meeting->id)
                            ->where('status', 'joined')
                            ->first();
                        if ($nextHost) {
                            $meeting->update(['host_id' => $nextHost->user_id]);
                            $newHostId = $nextHost->user_id;
                        }
                    }
                }
            }
        }

        $targetUserIds = $conversation->participants()
            ->where('user_id', '!=', $userId)
            ->pluck('user_id')
            ->toArray();
        $targetUserId = count($targetUserIds) === 1 ? (int)$targetUserIds[0] : null;

        // Neu cuoc goi 1-1 hoac phong bi dong cho tat ca -> gui end_call; neu chi 1 thanh vien roi nhom -> gui participant_left
        $leaveAction = (!$conversation->is_group || $isMeetingEnded) ? 'end_call' : 'participant_left';

        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: $leaveAction,
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar_url,
            targetUserId: $targetUserId,
            roomCode: $roomCode,
            callType: $request->input('call_type', 'video'),
            payload: [
                'left_user_id' => $userId,
                'left_user_name' => Auth::user()->name,
                'new_host_id' => $newHostId,
                'remaining_count' => $remainingParticipants,
                'is_group' => (bool)$conversation->is_group,
            ],
            targetUserIds: $targetUserIds
        ))->toOthers();

        return response()->json(['success' => true, 'message' => 'Đã rời cuộc gọi.']);
    }
}
