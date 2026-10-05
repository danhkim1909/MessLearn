<!-- Header Chat -->
<div class="h-16 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 shrink-0">
    <div class="flex items-center gap-3">
        @php
            $isGroup = $activeConversation->is_group;
            if ($isGroup) {
                $chatName = $activeConversation->name;
            } else {
                $otherUser = $activeConversation->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
                $chatName = $otherUser ? $otherUser->name : 'Người dùng';
            }
        @endphp
        @if($isGroup)
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 flex items-center justify-center font-bold">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        @else
            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold">
                {{ strtoupper(substr($chatName, 0, 1)) }}
            </div>
        @endif
        <div>
            <h2 class="font-extrabold text-slate-900 dark:text-white">{{ $chatName }}</h2>
            <p class="text-xs text-emerald-500 font-medium">Đang hoạt động</p>
        </div>
    </div>
</div>
