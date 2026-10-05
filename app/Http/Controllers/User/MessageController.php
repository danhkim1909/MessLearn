<?php

namespace App\Http\Controllers\User;

use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                Log::warning("Người dùng {$userId} cố gắng gửi tin nhắn vào cuộc trò chuyện {$conversation->id} mà không có quyền.");
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $hasAudio = $request->hasFile('audio');
            $hasImage = $request->hasFile('image');

            if ($hasAudio) {
                $request->validate([
                    'audio' => ['required', 'file', 'max:10240', 'extensions:webm,ogg,mp3,wav,m4a,mp4'],
                    'body' => 'nullable|string|max:2000',
                    'reply_to_id' => 'nullable|exists:messages,id',
                ]);

                $filePath = $request->file('audio')->store('audios', 'public');
                $type = 'audio';
            } elseif ($hasImage) {
                $request->validate([
                    'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
                    'body' => 'nullable|string|max:2000',
                    'reply_to_id' => 'nullable|exists:messages,id',
                ]);

                $filePath = $request->file('image')->store('images', 'public');
                $type = 'image';
            } else {
                $request->validate([
                    'body' => 'required|string|max:2000',
                    'reply_to_id' => 'nullable|exists:messages,id',
                ]);

                $filePath = null;
                $type = 'text';
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'type' => $type,
                'body' => $request->body,
                'file_path' => $filePath,
                'reply_to_id' => $request->reply_to_id,
            ]);

            $message->load(['user', 'replyTo.user', 'reactions']);

            $conversation->touch();

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Lỗi khi gửi tin nhắn trong cuộc trò chuyện {$conversation->id}: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi máy chủ'], 500);
        }
    }

    public function toggleReaction(Request $request, Conversation $conversation, Message $message)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $request->validate([
                'reaction' => 'required|string|in:like,heart,laugh,wow,sad,angry',
            ]);

            $existingReaction = MessageReaction::where('message_id', $message->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingReaction) {
                if ($existingReaction->reaction === $request->reaction) {
                    $existingReaction->delete();
                } else {
                    $existingReaction->update(['reaction' => $request->reaction]);
                }
            } else {
                MessageReaction::create([
                    'message_id' => $message->id,
                    'user_id' => $userId,
                    'reaction' => $request->reaction,
                ]);
            }

            $message->load(['user', 'replyTo.user', 'reactions']);

            broadcast(new MessageUpdated($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Lỗi khi thả cảm xúc: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi máy chủ'], 500);
        }
    }

    public function rollDice(Request $request, Conversation $conversation)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $request->validate([
                'body' => 'nullable|string|max:500',
            ]);

            $diceNumber = random_int(1, 6);

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'type' => 'game_dice',
                'body' => $request->body,
                'metadata' => [
                    'dice' => $diceNumber,
                ],
            ]);

            $message->load(['user', 'replyTo.user', 'reactions']);
            $conversation->touch();

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Lỗi khi tung xúc xắc: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi máy chủ'], 500);
        }
    }

    public function createRps(Request $request, Conversation $conversation)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $request->validate([
                'choice' => 'required|string|in:rock,paper,scissors',
                'body' => 'nullable|string|max:500',
            ]);

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'type' => 'game_rps',
                'body' => $request->body,
                'metadata' => [
                    'creator_id' => $userId,
                    'creator_name' => Auth::user()->name,
                    'creator_choice' => $request->choice,
                    'opponent_id' => null,
                    'opponent_name' => null,
                    'opponent_choice' => null,
                    'status' => 'waiting',
                    'winner_id' => null,
                ],
            ]);

            $message->load(['user', 'replyTo.user', 'reactions']);
            $conversation->touch();

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Lỗi khi tạo thách đấu Oẳn Tù Tì: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi máy chủ'], 500);
        }
    }

    public function playRps(Request $request, Conversation $conversation, Message $message)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            if ($message->type !== 'game_rps' || ($message->metadata['status'] ?? '') !== 'waiting') {
                return response()->json(['error' => 'Thách đấu không hợp lệ hoặc đã kết thúc'], 422);
            }

            if ($message->metadata['creator_id'] == $userId) {
                return response()->json(['error' => 'Bạn không thể tự chơi với chính mình'], 422);
            }

            $request->validate([
                'choice' => 'required|string|in:rock,paper,scissors',
            ]);

            $creatorChoice = $message->metadata['creator_choice'];
            $opponentChoice = $request->choice;

            if ($creatorChoice === $opponentChoice) {
                $winnerId = 'draw';
            } elseif (
                ($creatorChoice === 'rock' && $opponentChoice === 'scissors') ||
                ($creatorChoice === 'scissors' && $opponentChoice === 'paper') ||
                ($creatorChoice === 'paper' && $opponentChoice === 'rock')
            ) {
                $winnerId = $message->metadata['creator_id'];
            } else {
                $winnerId = $userId;
            }

            $metadata = $message->metadata;
            $metadata['creator_name'] = $metadata['creator_name'] ?? $message->user->name;
            $metadata['opponent_id'] = $userId;
            $metadata['opponent_name'] = Auth::user()->name;
            $metadata['opponent_choice'] = $opponentChoice;
            $metadata['status'] = 'completed';
            $metadata['winner_id'] = $winnerId;

            $message->update(['metadata' => $metadata]);

            $message->load(['user', 'replyTo.user', 'reactions']);
            $conversation->touch();

            broadcast(new MessageUpdated($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Lỗi khi tham gia Oẳn Tù Tì: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi máy chủ'], 500);
        }
    }

    public function loadMore(Request $request, Conversation $conversation)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $beforeId = (int) $request->query('before_id');
            $limit = min(max((int) $request->query('limit', 25), 1), 50);
            $targetId = (int) $request->query('target_id');

            if (!$beforeId) {
                return response()->json(['messages' => [], 'has_more' => false]);
            }

            if ($targetId > 0 && $targetId < $beforeId) {
                $messages = $conversation->messages()
                    ->where('id', '<', $beforeId)
                    ->where('id', '>=', $targetId)
                    ->with(['user', 'replyTo.user', 'quiz.submissions', 'reactions'])
                    ->latest('id')
                    ->take(60)
                    ->get()
                    ->reverse()
                    ->values();
            } else {
                $messages = $conversation->messages()
                    ->where('id', '<', $beforeId)
                    ->with(['user', 'replyTo.user', 'quiz.submissions', 'reactions'])
                    ->latest('id')
                    ->take($limit)
                    ->get()
                    ->reverse()
                    ->values();
            }

            $oldestId = $messages->first()?->id;
            $hasMore = $oldestId ? $conversation->messages()->where('id', '<', $oldestId)->exists() : false;

            return response()->json([
                'messages' => $messages,
                'has_more' => $hasMore,
                'oldest_id' => $oldestId,
            ]);
        } catch (Exception $e) {
            Log::error("Loi khi tai them tin nhan cu: " . $e->getMessage());
            return response()->json(['error' => 'Da xay ra loi may chu'], 500);
        }
    }

    public function search(Request $request, Conversation $conversation)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $keyword = trim((string) $request->query('q', ''));
            if ($keyword === '') {
                return response()->json(['results' => []]);
            }

            $messages = $conversation->messages()
                ->where('body', 'LIKE', '%' . $keyword . '%')
                ->with('user')
                ->latest('id')
                ->take(30)
                ->get();

            $results = $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_name' => $msg->user ? $msg->user->name : 'Nguoi dung',
                    'user_id' => $msg->user_id,
                    'body' => $msg->body,
                    'type' => $msg->type,
                    'created_at' => $msg->created_at ? $msg->created_at->format('H:i d/m/Y') : '',
                ];
            });

            return response()->json(['results' => $results]);
        } catch (Exception $e) {
            Log::error("Loi khi tim kiem tin nhan: " . $e->getMessage());
            return response()->json(['error' => 'Da xay ra loi may chu'], 500);
        }
    }

    public function togglePin(Request $request, Conversation $conversation, Message $message)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            if ((int)$message->conversation_id !== (int)$conversation->id) {
                return response()->json(['error' => 'Tin nhan khong thuoc cuoc tro chuyen nay'], 400);
            }

            $newPinStatus = !$message->is_pinned;

            if ($newPinStatus) {
                $conversation->messages()->where('is_pinned', true)->update(['is_pinned' => false]);
            }

            $message->update(['is_pinned' => $newPinStatus]);
            $message->load(['user', 'replyTo.user', 'quiz.submissions', 'reactions']);

            broadcast(new MessageUpdated($message))->toOthers();

            return response()->json([
                'is_pinned' => $message->is_pinned,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            Log::error("Loi khi ghim tin nhan: " . $e->getMessage());
            return response()->json(['error' => 'Da xay ra loi may chu'], 500);
        }
    }

    public function createEvent(Request $request, Conversation $conversation)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'remind_at' => 'required|string',
            ]);

            $currentUser = Auth::user();

            $metadata = [
                'title' => trim($request->input('title')),
                'remind_at' => $request->input('remind_at'),
                'remind_before' => (int)$request->input('remind_before', 15),
                'location' => trim((string)$request->input('location', '')),
                'note' => trim((string)$request->input('note', '')),
                'participants' => [
                    $userId => [
                        'id' => $userId,
                        'name' => $currentUser->name,
                        'joined_at' => now()->toIso8601String(),
                    ]
                ],
            ];

            $message = $conversation->messages()->create([
                'user_id' => $userId,
                'type' => 'event',
                'body' => trim($request->input('title')),
                'metadata' => $metadata,
            ]);

            $conversation->touch();
            $message->load(['user', 'replyTo.user', 'quiz.submissions', 'reactions']);

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Loi khi tao lich hen: " . $e->getMessage());
            return response()->json(['error' => 'Da xay ra loi may chu'], 500);
        }
    }

    public function toggleJoinEvent(Request $request, Conversation $conversation, Message $message)
    {
        try {
            $userId = Auth::id();

            if (!$conversation->participants()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            if ((int)$message->conversation_id !== (int)$conversation->id || $message->type !== 'event') {
                return response()->json(['error' => 'Yeu cau khong hop le'], 400);
            }

            $metadata = $message->metadata ?? [];
            $participants = $metadata['participants'] ?? [];

            if (isset($participants[$userId])) {
                unset($participants[$userId]);
            } else {
                $currentUser = Auth::user();
                $participants[$userId] = [
                    'id' => $userId,
                    'name' => $currentUser->name,
                    'joined_at' => now()->toIso8601String(),
                ];
            }

            $metadata['participants'] = $participants;
            $message->metadata = $metadata;
            $message->save();

            $message->load(['user', 'replyTo.user', 'quiz.submissions', 'reactions']);

            broadcast(new MessageUpdated($message))->toOthers();

            return response()->json($message);
        } catch (Exception $e) {
            Log::error("Loi khi tham gia lich hen: " . $e->getMessage());
            return response()->json(['error' => 'Da xay ra loi may chu'], 500);
        }
    }
}
