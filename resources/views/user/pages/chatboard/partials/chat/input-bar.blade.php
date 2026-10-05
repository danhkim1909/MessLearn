<!-- Khung nhập Chat -->
<div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 shrink-0 flex flex-col">
    <!-- Typing Indicator (Trang thai dang soan tin nhan) -->
    <div id="typing-indicator" class="hidden mb-2 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs w-fit items-center gap-2 border border-slate-200/60 dark:border-slate-700/60 shadow-xs">
        <div class="flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-bounce" style="animation-delay: 0ms;"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-bounce" style="animation-delay: 150ms;"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-bounce" style="animation-delay: 300ms;"></span>
        </div>
        <span id="typing-indicator-text" class="font-medium text-[11px]"></span>
    </div>

    <!-- Preview Trả lời tin nhắn -->
    <div id="reply-preview-container" class="hidden mb-3 mx-12 p-3 bg-slate-50 dark:bg-slate-800/80 border-l-4 border-sky-500 rounded-xl flex items-center justify-between">
        <div class="text-xs min-w-0 flex-1">
            <div class="font-bold text-slate-700 dark:text-slate-300 mb-0.5">Đang trả lời: <span id="reply-to-name"></span></div>
            <div class="text-slate-500 dark:text-slate-400 truncate" id="reply-to-text"></div>
        </div>
        <button type="button" onclick="cancelReply()" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors ml-3 shrink-0">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- Preview Ảnh đính kèm & Nút sửa chú thích -->
    <div id="image-preview-container" class="hidden mb-3 mx-12 p-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl items-center gap-3">
        <div class="relative w-12 h-12 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
            <img id="image-preview-thumbnail" src="" class="w-full h-full object-cover">
        </div>
        <div class="flex-1 min-w-0">
            <span id="image-preview-filename" class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate block"></span>
            <span class="text-[10px] text-slate-400">Ảnh đính kèm</span>
        </div>
        <button type="button" onclick="annotateSelectedImage()" class="px-2.5 py-1.5 bg-sky-50 dark:bg-sky-900/30 text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-900/50 rounded-lg text-xs font-medium flex items-center gap-1 transition-colors shrink-0" title="Vẽ chú thích trước khi gửi">
            <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
            Vẽ lên ảnh
        </button>
        <button type="button" onclick="cancelImageSelection()" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shrink-0">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- Form gửi tin nhắn chính -->
    <form id="chat-form" class="flex items-end gap-2" onsubmit="sendChatMessage(event)">
        <input type="hidden" id="reply-to-id" value="">
        <input type="file" id="image-file-input" accept="image/*" class="hidden" onchange="handleImageSelected(event)">
        <button type="button" onclick="document.getElementById('image-file-input').click()" class="p-3 text-slate-400 hover:text-sky-500 transition-colors" title="Đính kèm ảnh">
            <i data-lucide="paperclip" class="w-5 h-5"></i>
        </button>
        <div class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-1 relative">
            <textarea id="chat-input" rows="1" oninput="handleChatInputTyping()" class="w-full bg-transparent px-3 py-2 text-sm focus:outline-none dark:text-white resize-none max-h-32" placeholder="Nhập tin nhắn..." onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendChatMessage(event); }"></textarea>
        </div>
        <button type="button" id="btn-record-voice" onclick="startVoiceRecording()" class="p-3 text-slate-400 hover:text-sky-500 transition-colors rounded-xl flex items-center justify-center shrink-0" title="Ghi âm">
            <i data-lucide="mic" class="w-5 h-5"></i>
        </button>
        <button type="submit" class="p-3 bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow-md shadow-sky-500/20 transition-all flex items-center justify-center shrink-0">
            <i data-lucide="send" class="w-5 h-5 ml-1"></i>
        </button>
    </form>

    <!-- Khay Ghi âm Tin nhắn thoại -->
    <div id="voice-recording-container" class="hidden items-center gap-3 w-full bg-slate-50 dark:bg-slate-800 border border-sky-400/50 dark:border-sky-500/50 rounded-2xl p-2 px-4">
        <button type="button" onclick="cancelVoiceRecording()" class="p-2 text-slate-400 hover:text-rose-500 transition-colors rounded-xl flex items-center justify-center shrink-0" title="Hủy">
            <i data-lucide="trash-2" class="w-5 h-5"></i>
        </button>
        <div id="recording-active-view" class="flex-1 flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse shrink-0"></span>
            <span id="recording-timer" class="text-xs font-mono font-bold text-slate-700 dark:text-slate-200 shrink-0">00:00</span>
            <div class="flex-1 flex items-center gap-1 h-4 overflow-hidden opacity-60">
                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-2"></span>
                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-4"></span>
                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-3"></span>
                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-2"></span>
                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-4"></span>
            </div>
            <button type="button" onclick="stopAndPreviewVoiceRecording()" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-medium rounded-lg flex items-center gap-1.5 transition-colors shrink-0">
                <i data-lucide="square" class="w-3.5 h-3.5"></i>
                Nghe thử
            </button>
        </div>
        <div id="recording-preview-view" class="hidden flex-1 flex items-center gap-3">
            <button type="button" onclick="togglePreviewAudio()" class="w-8 h-8 rounded-full bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="play" id="preview-play-icon" class="w-4 h-4 ml-0.5"></i>
                <i data-lucide="pause" id="preview-pause-icon" class="w-4 h-4 hidden"></i>
            </button>
            <span id="preview-timer" class="text-xs font-mono text-slate-600 dark:text-slate-300 shrink-0">00:00</span>
            <audio id="preview-audio-element" class="hidden" ontimeupdate="updatePreviewTimer()" onended="onPreviewAudioEnded()"></audio>
            <div class="flex-1 text-xs text-slate-400 truncate">Sẵn sàng gửi</div>
        </div>
        <button type="button" onclick="sendVoiceMessage()" class="p-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow-md shadow-sky-500/20 transition-all flex items-center justify-center shrink-0" title="Gửi ghi âm">
            <i data-lucide="send" class="w-4 h-4 ml-0.5"></i>
        </button>
    </div>
</div>
