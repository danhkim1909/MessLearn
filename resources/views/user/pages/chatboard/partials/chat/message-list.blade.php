<!-- Nội dung Chat -->
<div class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-messages-container" onscroll="handleChatScroll()" data-has-more="{{ ($hasMoreMessages ?? false) ? '1' : '0' }}" data-oldest-id="{{ $oldestMessageId ?? ($activeConversation?->messages->first()?->id ?? 0) }}">
    <!-- Spinner báo đang tải tin nhắn cũ -->
    <div id="loading-old-messages" class="hidden py-2 text-center text-xs text-slate-400">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin text-sky-500"></i>
            <span class="font-medium text-[11px]">Đang tải tin nhắn cũ...</span>
        </div>
    </div>

    @if($activeConversation->messages->isEmpty())
        <div class="h-full flex items-center justify-center text-sm text-slate-400" id="empty-messages-placeholder">
            Chưa có tin nhắn nào. Bắt đầu trò chuyện!
        </div>
    @else
        @foreach($activeConversation->messages as $message)
            @include('user.pages.chatboard.partials.chat.message-item', ['message' => $message])
        @endforeach
    @endif
</div>

<!-- Nút cuộn xuống đáy & Thông báo tin mới -->
<button type="button" id="btn-scroll-bottom" onclick="smartScrollToBottom(true, true)" class="hidden absolute right-6 bottom-24 p-2.5 rounded-full bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 shadow-lg border border-slate-200 dark:border-slate-700 hover:text-sky-500 hover:scale-105 active:scale-95 transition-all z-20 items-center gap-1.5 text-xs font-semibold" title="Cuộn xuống đáy">
    <i data-lucide="arrow-down" class="w-4 h-4 text-sky-500"></i>
    <span id="scroll-bottom-badge" class="hidden w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
</button>
