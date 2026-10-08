<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Lay danh sach cong viec va thanh vien trong cuoc tro chuyen
     */
    public function index(Conversation $conversation)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        $tasks = $conversation->tasks()
            ->with(['creator', 'assignee'])
            ->orderByRaw("CASE status WHEN 'todo' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'done' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->latest('id')
            ->get();

        // Danh sach thanh vien de phan cong cong viec
        $members = $conversation->participants()
            ->with('user')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->user->id,
                    'name' => $participant->user->name,
                    'avatar_url' => $participant->user->avatar_url,
                    'role' => $participant->role,
                ];
            });

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'members' => $members,
            'counts' => [
                'total' => $tasks->count(),
                'todo' => $tasks->where('status', 'todo')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'done' => $tasks->where('status', 'done')->count(),
            ]
        ]);
    }

    /**
     * Tao mot cong viec moi trong nhom
     */
    public function store(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        // Kiem tra neu co assignee thi phai la thanh vien trong nhom
        if (!empty($validated['assignee_id'])) {
            if (!$conversation->participants()->where('user_id', $validated['assignee_id'])->exists()) {
                return response()->json(['message' => 'Người được giao không thuộc cuộc trò chuyện này.'], 422);
            }
        }

        $task = Task::create([
            'conversation_id' => $conversation->id,
            'creator_id' => $userId,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'title' => trim($validated['title']),
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'status' => 'todo',
        ]);

        $task->load(['creator', 'assignee']);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Đã tạo công việc mới thành công.',
        ], 201);
    }

    /**
     * Cap nhat nhanh trang thai cong viec (todo, in_progress, done)
     */
    public function updateStatus(Request $request, Conversation $conversation, Task $task)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        if ($task->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Công việc không thuộc cuộc trò chuyện này.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task->status = $validated['status'];
        $task->save();

        $task->load(['creator', 'assignee']);

        $statusLabels = [
            'todo' => 'Cần làm',
            'in_progress' => 'Đang làm',
            'done' => 'Đã hoàn thành',
        ];

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Đã chuyển trạng thái sang: ' . ($statusLabels[$task->status] ?? $task->status),
        ]);
    }

    /**
     * Cap nhat chi tiet cong viec
     */
    public function update(Request $request, Conversation $conversation, Task $task)
    {
        $userId = Auth::id();
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        if ($task->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Công việc không thuộc cuộc trò chuyện này.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'nullable|in:todo,in_progress,done',
        ]);

        if (!empty($validated['assignee_id'])) {
            if (!$conversation->participants()->where('user_id', $validated['assignee_id'])->exists()) {
                return response()->json(['message' => 'Người được giao không thuộc cuộc trò chuyện này.'], 422);
            }
        }

        $task->title = trim($validated['title']);
        $task->description = $validated['description'] ? trim($validated['description']) : null;
        $task->assignee_id = $validated['assignee_id'] ?? null;
        $task->due_date = $validated['due_date'] ?? null;
        $task->priority = $validated['priority'];
        if (!empty($validated['status'])) {
            $task->status = $validated['status'];
        }
        $task->save();

        $task->load(['creator', 'assignee']);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Đã cập nhật công việc thành công.',
        ]);
    }

    /**
     * Xoa cong viec
     */
    public function destroy(Conversation $conversation, Task $task)
    {
        $userId = Auth::id();
        $participant = $conversation->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return response()->json(['message' => 'Bạn không thuộc cuộc trò chuyện này.'], 403);
        }

        if ($task->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Công việc không thuộc cuộc trò chuyện này.'], 404);
        }

        // Cho phep nguoi tao task hoac quan tri vien nhom xoa
        $isAdmin = in_array($participant->role, ['admin', 'owner']);
        if ($task->creator_id !== $userId && !$isAdmin) {
            return response()->json(['message' => 'Chỉ người tạo việc hoặc Quản trị viên mới có thể xóa công việc này.'], 403);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa công việc thành công.',
        ]);
    }
}
