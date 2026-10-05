<!-- Header Chat -->
<div class="relative border-b border-slate-200 dark:border-slate-800 shrink-0">
    <div class="h-16 flex items-center justify-between px-6">
        <div class="flex items-center gap-3">
            @php
                $isGroup = $activeConversation->is_group;
                if ($isGroup) {
                    $chatName = $activeConversation->name;
                } else {
                    $otherUser = $activeConversation->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
                    $chatName = $otherUser ? $otherUser->name : 'Nguoi dung';
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
                <p id="chat-header-status" class="text-xs text-emerald-500 font-medium">Đang hoạt động</p>
            </div>
        </div>

        <!-- Nut mo tim kiem tin nhan -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleChatSearch()" class="p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Tìm kiếm tin nhắn">
                <i data-lucide="search" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- Thanh tim kiem tin nhan -->
    <div id="chat-search-panel" class="hidden absolute top-full left-0 right-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 p-3 z-30 shadow-lg">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" id="chat-search-input" oninput="handleChatSearchInput(this.value)" placeholder="Nhập từ khóa tìm kiếm tin nhắn..." class="w-full pl-9 pr-8 py-2 text-xs bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-sky-500 dark:text-white">
                <button type="button" onclick="clearChatSearchInput()" id="btn-clear-search" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <button type="button" onclick="closeChatSearch()" class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Đóng tìm kiếm">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Danh sach ket qua tim kiem -->
        <div id="chat-search-results-container" class="mt-2 max-h-60 overflow-y-auto space-y-1 hidden"></div>
        <div id="chat-search-status" class="hidden mt-2 text-[11px] text-center text-slate-400 py-1"></div>
    </div>

    <!-- Thanh tin nhan duoc ghim (Pinned Message Bar) -->
    <div id="pinned-message-bar" class="{{ ($pinnedMessage ?? false) ? 'flex' : 'hidden' }} items-center justify-between px-6 py-2 bg-sky-50/90 dark:bg-sky-950/40 border-t border-sky-100 dark:border-sky-900/40 text-xs transition-all z-20" data-pinned-id="{{ $pinnedMessage?->id ?? 0 }}">
        <div class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer hover:opacity-80 transition-opacity" onclick="jumpToPinnedMessage()">
            <div class="w-6 h-6 rounded-lg bg-sky-500/10 text-sky-500 flex items-center justify-center shrink-0">
                <i data-lucide="pin" class="w-3.5 h-3.5 fill-sky-500/30"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 font-bold text-sky-900 dark:text-sky-200 text-[11px]">
                    <span>Tin nhắn đã ghim:</span>
                    <span id="pinned-sender-name" class="font-semibold text-slate-600 dark:text-slate-300">{{ $pinnedMessage?->user?->name ?? '' }}</span>
                </div>
                <div id="pinned-message-preview" class="text-slate-500 dark:text-slate-400 truncate text-[11px]">
                    @if($pinnedMessage ?? false)
                        @if($pinnedMessage->type === 'image')
                            [Hình ảnh] {{ $pinnedMessage->body ? ': ' . $pinnedMessage->body : '' }}
                        @elseif($pinnedMessage->type === 'audio')
                            [Tin nhắn thoại]
                        @elseif($pinnedMessage->type === 'quiz')
                            [Bài kiểm tra]: {{ $pinnedMessage->body }}
                        @elseif($pinnedMessage->type === 'game_dice')
                            [Tung xúc xắc]
                        @elseif($pinnedMessage->type === 'game_rps')
                            [Oẳn tù tì]
                        @else
                            {{ $pinnedMessage->body }}
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <button type="button" onclick="unpinCurrentMessage(event)" class="p-1 text-slate-400 hover:text-rose-500 rounded-md hover:bg-black/5 dark:hover:bg-white/5 transition-colors shrink-0 ml-2" title="Bỏ ghim">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>
</div>
