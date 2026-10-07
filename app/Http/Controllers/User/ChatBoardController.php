<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class ChatBoardController extends Controller
{
    private function getSidebarData()
    {
        $userId = Auth::id();

        $pendingRequests = Friendship::with('sender')
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->get();

        $friends = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)->orWhere('friend_id', $userId);
            })
            ->with(['sender', 'receiver'])
            ->get()
            ->map(function ($friendship) use ($userId) {
                return $friendship->user_id == $userId ? $friendship->receiver : $friendship->sender;
            });

        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['participants.user', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('updated_at')
            ->get();

        return compact('pendingRequests', 'friends', 'conversations');
    }

    public function index()
    {
        return view('user.pages.chatboard.index', $this->getSidebarData());
    }

    public function show(\App\Models\Conversation $conversation)
    {
        $userId = Auth::id();

        // Check if user is in conversation
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            abort(403);
        }

        $conversation->load([
            'participants.user',
            'messages' => function ($query) {
                $query->latest('id')
                    ->take(30)
                    ->with(['user', 'replyTo.user', 'quiz.submissions', 'reactions']);
            }
        ]);

        // Đảo ngược để hiển thị từ cũ đến mới theo chiều dọc
        $conversation->setRelation('messages', $conversation->messages->reverse()->values());

        $oldestMessageId = $conversation->messages->first()?->id ?? null;
        $hasMoreMessages = $oldestMessageId
            ? $conversation->messages()->where('id', '<', $oldestMessageId)->exists()
            : false;

        $pinnedMessage = $conversation->pinnedMessages()->with('user')->latest('updated_at')->first();

        $now = now();
        $upcomingEvent = $conversation->messages()
            ->where('type', 'event')
            ->latest('id')
            ->take(50)
            ->get()
            ->filter(function ($msg) use ($now) {
                $remindAt = $msg->metadata['remind_at'] ?? null;
                if (!$remindAt) return false;
                try {
                    $dt = \Carbon\Carbon::parse($remindAt);
                    return $dt->greaterThanOrEqualTo($now);
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->sortBy(function ($msg) {
                return $msg->metadata['remind_at'] ?? '';
            })
            ->first();
        
        $activeMeeting = $conversation->meetings()
            ->whereIn('status', ['ringing', 'ongoing'])
            ->with(['host'])
            ->latest('id')
            ->first();

        $data = $this->getSidebarData();
        $data['activeConversation'] = $conversation;
        $data['hasMoreMessages'] = $hasMoreMessages;
        $data['oldestMessageId'] = $oldestMessageId;
        $data['pinnedMessage'] = $pinnedMessage;
        $data['upcomingEvent'] = $upcomingEvent;
        $data['activeMeeting'] = $activeMeeting;

        return view('user.pages.chatboard.index', $data);
    }
}
