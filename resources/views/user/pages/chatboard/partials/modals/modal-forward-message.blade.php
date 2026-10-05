<!-- MODAL: CHUYEN TIEP TIN NHAN -->
<div id="modal-forward-message" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-lg rounded-3xl shadow-2xl flex flex-col overflow-hidden mx-4">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 p-5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center">
                    <i data-lucide="forward" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Chuyển tiếp tin nhắn</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Chia sẻ tin nhắn sang các cuộc trò chuyện khác</p>
                </div>
            </div>
            <button type="button" onclick="closeForwardModal()" class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <!-- Xem truoc noi dung tin nhan can chuyen tiep -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nội dung chuyển tiếp</label>
                <div id="forward-preview-box" class="p-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 rounded-2xl flex items-center gap-3">
                    <div id="forward-preview-icon" class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div id="forward-preview-sender" class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate">Tin nhắn</div>
                        <div id="forward-preview-text" class="text-xs text-slate-500 dark:text-slate-400 truncate">Đang tải nội dung...</div>
                    </div>
                </div>
            </div>

            <!-- O tim kiem hoi thoai dich -->
            <div class="relative">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" id="forward-search-input" oninput="filterForwardConversations()" placeholder="Tìm cuộc trò chuyện hoặc người nhận..." class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-indigo-500 dark:text-white transition-colors">
            </div>

            <!-- Danh sach cuoc tro chuyen -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Gửi đến</label>
                    <span id="forward-selected-count" class="text-[11px] font-bold text-indigo-500">Đã chọn 0 cuộc trò chuyện</span>
                </div>
                <div id="forward-conversations-list" class="max-h-56 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                    @forelse($conversations ?? [] as $conv)
                        @php
                            $isGroup = $conv->is_group;
                            if ($isGroup) {
                                $convName = $conv->name ?? 'Nhóm học tập';
                                $avatarChar = strtoupper(substr($convName, 0, 1));
                                $convBadge = 'Nhóm';
                            } else {
                                $otherUser = $conv->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
                                $convName = $otherUser ? $otherUser->name : 'Người dùng';
                                $avatarChar = strtoupper(substr($convName, 0, 1));
                                $convBadge = 'Cá nhân';
                            }
                        @endphp
                        <div onclick="toggleForwardConv({{ $conv->id }})" data-conv-id="{{ $conv->id }}" data-conv-name="{{ strtolower($convName) }}" class="forward-conv-item flex items-center justify-between p-2.5 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-750 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 cursor-pointer transition-all select-none">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl {{ $isGroup ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $avatarChar }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate">{{ $convName }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $convBadge }}</p>
                                </div>
                            </div>
                            <div class="shrink-0 ml-2">
                                <input type="checkbox" id="forward-chk-{{ $conv->id }}" value="{{ $conv->id }}" class="forward-checkbox w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 cursor-pointer pointer-events-none">
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Không tìm thấy cuộc trò chuyện nào</p>
                    @endforelse
                </div>
            </div>

            <div id="forward-message-error" class="hidden text-xs text-rose-500 font-medium"></div>

            <!-- Footer Modal -->
            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                <button type="button" onclick="closeForwardModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Hủy
                </button>
                <button type="button" id="btn-submit-forward" onclick="submitForwardMessage()" disabled class="px-5 py-2.5 rounded-xl bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span id="forward-btn-text">Gửi</span>
                </button>
            </div>
        </div>
    </div>
</div>
