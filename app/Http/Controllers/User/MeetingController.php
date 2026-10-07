<?php

namespace App\Http\Controllers\User;

use App\Events\CallSignalEvent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Message;
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

        $type = $request->input('type', 'video');
        if (!in_array($type, ['voice', 'video'])) {
            $type = 'video';
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

            return response()->json([
                'success' => true,
                'meeting' => $activeMeeting,
                'room_code' => $activeMeeting->room_code,
                'is_existing' => true,
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

        // Phat su kien cuoc goi den qua Reverb
        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: 'incoming_call',
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar,
            roomCode: $roomCode,
            callType: $type,
            payload: [
                'meeting_id' => $meeting->id,
                'is_group' => $conversation->is_group,
                'title' => $conversation->name,
            ]
        ))->toOthers();

        return response()->json([
            'success' => true,
            'meeting' => $meeting,
            'room_code' => $roomCode,
            'is_existing' => false,
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
        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: $action,
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar,
            targetUserId: $targetUserId ? (int)$targetUserId : null,
            roomCode: $roomCode,
            callType: $callType,
            payload: $payload
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
                    'body' => 'Cuoc goi nho',
                    'metadata' => [
                        'call_status' => 'missed',
                        'type' => $meeting->type,
                    ],
                ]);
            }
        }

        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: 'reject_call',
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar,
            roomCode: $roomCode,
            callType: $request->input('call_type', 'video')
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    // --- Roi phong / Ket thuc cuoc goi ---
    public function leave(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $roomCode = $request->input('room_code');

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

                // Neu khong con ai hoac cuoc goi 1-1 thi ket thuc
                if ($remainingParticipants <= 1 || !$conversation->is_group) {
                    if ($meeting->status !== 'ended') {
                        $meeting->update([
                            'status' => 'ended',
                            'ended_at' => now(),
                        ]);

                        $durationStr = $meeting->duration_formatted;
                        $typeName = $meeting->type === 'voice' ? 'Cuoc goi thoai' : 'Cuoc goi video';

                        Message::create([
                            'conversation_id' => $conversation->id,
                            'user_id' => $meeting->host_id,
                            'type' => 'text',
                            'body' => "{$typeName} da ket thuc. Thoi luong: {$durationStr}",
                            'metadata' => [
                                'call_status' => 'ended',
                                'duration_seconds' => $meeting->duration_seconds,
                                'duration_formatted' => $durationStr,
                                'type' => $meeting->type,
                            ],
                        ]);
                    }
                }
            }
        }

        broadcast(new CallSignalEvent(
            conversationId: $conversation->id,
            action: 'end_call',
            senderId: $userId,
            senderName: Auth::user()->name,
            senderAvatar: Auth::user()->avatar,
            roomCode: $roomCode,
            callType: $request->input('call_type', 'video')
        ))->toOthers();

        return response()->json(['success' => true, 'message' => 'Da roi cuoc goi.']);
    }
}
