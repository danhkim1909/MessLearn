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

        $conversation->load(['messages.user', 'messages.quiz.submissions', 'participants.user']);
        
        $data = $this->getSidebarData();
        $data['activeConversation'] = $conversation;

        return view('user.pages.chatboard.index', $data);
    }
}
