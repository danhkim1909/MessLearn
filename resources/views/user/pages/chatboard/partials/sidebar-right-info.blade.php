<!-- CỘT 4: RIGHT SIDEBAR - THÔNG TIN CUỘC TRÒ CHUYỆN (ZALO STYLE) -->
@php
    $isGroup = $activeConversation->is_group;
    $customNickname = $currentParticipant?->nickname;
    if ($isGroup) {
        $originalName = $activeConversation->name;
        $chatName = $customNickname ?: $originalName;
    } else {
        $otherUser = $activeConversation->participants->where('user_id', '!=', Auth::id())->first()?->user ?? null;
        $originalName = $otherUser ? $otherUser->name : 'Người dùng';
        $chatName = $customNickname ?: $originalName;
    }
    $isPinned = $currentParticipant?->is_pinned ?? false;
    $isMuted = $currentParticipant ? $currentParticipant->isMuted() : false;
@endphp

<aside id="sidebar-right-info" class="w-80 bg-slate-50 dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 flex flex-col shrink-0 overflow-y-auto transition-all duration-200">
    <!-- Header của Panel thông tin -->
    <div class="h-16 px-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Thông tin hội thoại</h3>
        <button type="button" onclick="toggleRightSidebar()" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Đóng bảng thông tin">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- Phân khu 1: Thẻ hồ sơ hội thoại (Profile Card) -->
    <div class="p-5 flex flex-col items-center text-center border-b border-slate-200/80 dark:border-slate-800 bg-white/60 dark:bg-slate-800/40">
        @if($isGroup)
            <div class="relative group/avatar">
                <div class="w-20 h-20 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 flex items-center justify-center font-bold text-2xl overflow-hidden shadow-xs">
                    @if($activeConversation->avatar_url)
                        <img src="{{ $activeConversation->avatar_url }}" alt="{{ $chatName }}" class="w-full h-full object-cover">
                    @else
                        <i data-lucide="users" class="w-8 h-8"></i>
                    @endif
                </div>
                @if(($currentParticipant?->role ?? '') === 'admin')
                    <button type="button" onclick="openGroupMembersModal(); switchGroupModalTab('info');" class="absolute -bottom-1 -right-1 p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md transition-transform hover:scale-110" title="Đổi ảnh đại diện nhóm">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                    </button>
                @endif
            </div>
        @else
            <div class="w-20 h-20 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-2xl overflow-hidden shadow-xs">
                @if(isset($otherUser) && $otherUser && $otherUser->avatar_url)
                    <img src="{{ $otherUser->avatar_url }}" alt="{{ $chatName }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($chatName, 0, 1)) }}
                @endif
            </div>
        @endif

        <div class="mt-3 w-full">
            <div class="flex items-center justify-center gap-1.5">
                <h3 class="font-extrabold text-base text-slate-900 dark:text-white truncate max-w-[210px]">{{ $chatName }}</h3>
                <button type="button" 
                        id="btn-header-nickname" 
                        onclick="openChangeNicknameModal()" 
                        class="p-1 text-slate-400 hover:text-indigo-500 hover:bg-slate-200/60 dark:hover:bg-slate-700/60 rounded-lg transition-colors shrink-0" 
                        title="Đặt biệt danh">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            @if($customNickname && $customNickname !== $originalName)
                <p class="text-[11px] text-slate-400 mt-0.5 truncate">Tên gốc: {{ $originalName }}</p>
            @endif
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-center gap-1.5">
                @if($isGroup)
                    <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                    <span>{{ $activeConversation->participants->count() }} thành viên</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>Đang hoạt động</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Phân khu 2: Thao tác nhanh (Quick Actions) -->
    <div class="p-4 border-b border-slate-200/80 dark:border-slate-800">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Thao tác nhanh</p>
        <div class="grid grid-cols-4 gap-2">
            <!-- 1. Bật/Tắt chuông thông báo -->
            <div class="relative" id="header-mute-container">
                <button type="button" 
                        id="btn-header-mute-chat" 
                        onclick="toggleMuteDropdown(event)" 
                        class="w-full flex flex-col items-center justify-center py-2.5 px-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 hover:border-sky-500 transition-all group"
                        title="{{ $isMuted ? 'Đang tắt thông báo (Nhấn để tùy chỉnh)' : 'Tắt thông báo' }}">
                    <div class="w-8 h-8 rounded-lg {{ $isMuted ? 'bg-rose-50 dark:bg-rose-950/30 text-rose-500' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300' }} flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                        <i data-lucide="{{ $isMuted ? 'bell-off' : 'bell' }}" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 truncate max-w-full">Báo chuông</span>
                </button>
                <div id="header-mute-dropdown" class="hidden absolute left-0 bottom-full mb-2 w-52 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl py-1.5 z-50 text-xs">
                    <div class="px-3.5 py-1.5 font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-700/60 uppercase tracking-wider text-[10px]">
                        Cài đặt thông báo
                    </div>
                    <div id="mute-unmute-option-wrap" class="{{ $isMuted ? '' : 'hidden' }}">
                        <button type="button" onclick="handleSelectMuteDuration('unmute')" class="w-full text-left px-3.5 py-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 font-semibold flex items-center gap-2">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span>Bật lại thông báo</span>
                        </button>
                        <div class="border-t border-slate-100 dark:border-slate-700/60 my-1"></div>
                    </div>
                    <button type="button" onclick="handleSelectMuteDuration('1h')" class="w-full text-left px-3.5 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Tắt trong 1 giờ</span>
                        <span class="text-[10px] text-slate-400">1h</span>
                    </button>
                    <button type="button" onclick="handleSelectMuteDuration('8h')" class="w-full text-left px-3.5 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Tắt trong 8 giờ</span>
                        <span class="text-[10px] text-slate-400">8h</span>
                    </button>
                    <button type="button" onclick="handleSelectMuteDuration('forever')" class="w-full text-left px-3.5 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Cho đến khi mở lại</span>
                        <i data-lucide="bell-off" class="w-3.5 h-3.5 text-slate-400"></i>
                    </button>
                </div>
            </div>

            <!-- 2. Ghim cuộc trò chuyện -->
            <button type="button" 
                    id="btn-header-pin-chat" 
                    onclick="handleTogglePinChat()" 
                    class="w-full flex flex-col items-center justify-center py-2.5 px-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 hover:border-amber-500 transition-all group"
                    title="{{ $isPinned ? 'Bỏ ghim cuộc trò chuyện' : 'Ghim cuộc trò chuyện lên đầu' }}">
                <div class="w-8 h-8 rounded-lg {{ $isPinned ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-500' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300' }} flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                    <i data-lucide="pin" class="w-4 h-4 {{ $isPinned ? 'fill-amber-500 text-amber-500' : '' }}"></i>
                </div>
                <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 truncate max-w-full">Ghim chat</span>
            </button>

            <!-- 3. Bảng phân công việc -->
            <button type="button" 
                    onclick="openTaskBoardModal()" 
                    class="w-full flex flex-col items-center justify-center py-2.5 px-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 hover:border-indigo-500 transition-all group"
                    title="Bảng phân công việc & Nhiệm vụ">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                </div>
                <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 truncate max-w-full">Việc nhóm</span>
            </button>

            <!-- 4. Phòng học trực tuyến -->
            <button type="button" 
                    onclick="openMeetingLobby('video')" 
                    class="w-full flex flex-col items-center justify-center py-2.5 px-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 hover:border-emerald-500 transition-all group"
                    title="Phòng học & Họp trực tuyến">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                    <i data-lucide="presentation" class="w-4 h-4"></i>
                </div>
                <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 truncate max-w-full">Phòng học</span>
            </button>
        </div>
    </div>

    <!-- Phân khu 3: Danh sách & Quản lý thành viên -->
    @if($isGroup)
    <div class="p-4 border-b border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-bold text-xs text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-500"></i>
                <span>Thành viên nhóm ({{ $activeConversation->participants->count() }})</span>
            </h4>
            @if($activeConversation->canMemberInvite() || (($currentParticipant?->role ?? '') === 'admin'))
                <button type="button" onclick="openGroupMembersModal(); toggleAddMemberPanel();" class="p-1 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg text-[11px] font-bold flex items-center gap-1 transition-colors" title="Thêm bạn vào nhóm">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Thêm</span>
                </button>
            @endif
        </div>
        <div class="space-y-2">
            @foreach($activeConversation->participants->take(4) as $part)
                <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden">
                            @if($part->user && $part->user->avatar_url)
                                <img src="{{ $part->user->avatar_url }}" alt="{{ $part->user->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($part->user?->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">
                                {{ $part->nickname ?: ($part->user?->name ?? 'Người dùng') }}
                                @if($part->user_id === Auth::id())
                                    <span class="text-[10px] text-slate-400 font-normal">(Bạn)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($part->role === 'admin')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 shrink-0">Trưởng nhóm</span>
                    @endif
                </div>
            @endforeach
        </div>
        <button type="button" onclick="openGroupMembersModal()" class="w-full mt-3 py-2 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-colors">
            Xem tất cả {{ $activeConversation->participants->count() }} thành viên
        </button>
    </div>
    @else
    <div class="p-4 border-b border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-bold text-xs text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="user-round" class="w-3.5 h-3.5 text-sky-500"></i>
                <span>Thông tin bạn học</span>
            </h4>
        </div>
        <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 space-y-2 text-xs">
            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                <span class="text-slate-400">Họ và tên:</span>
                <span class="font-bold text-slate-900 dark:text-white truncate ml-2">{{ $otherUser?->name ?? 'Người dùng' }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                <span class="text-slate-400">Email:</span>
                <span class="truncate ml-2 text-slate-700 dark:text-slate-300">{{ $otherUser?->email ?? '' }}</span>
            </div>
            <button type="button" onclick="openPartnerProfileModal({{ $otherUser?->id ?? 0 }})" class="w-full mt-2 py-1.5 px-3 bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 font-bold rounded-xl text-center hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-colors">
                Xem chi tiết hồ sơ
            </button>
        </div>
    </div>
    @endif

    <!-- Phân khu 4: Kho tài liệu & Học tập -->
    <div class="p-4 border-b border-slate-200/80 dark:border-slate-800 space-y-3">
        <h4 class="font-bold text-xs text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
            <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-sky-500"></i>
            <span>Công cụ học tập & Tài liệu</span>
        </h4>

        <!-- Nút Tạo Quiz -->
        <button type="button" onclick="openModal('modal-create-quiz')" class="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 rounded-xl hover:border-sky-500 transition-all group">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/30 text-sky-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i data-lucide="pen-tool" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <p class="font-bold text-xs text-slate-900 dark:text-white">Tạo Quiz</p>
                    <p class="text-[10px] text-slate-400">Bài kiểm tra & Khảo sát</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 group-hover:text-sky-500 transition-colors"></i>
        </button>

        <!-- Nút Lịch hẹn nhóm -->
        <button type="button" onclick="openModal('modal-create-event')" class="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 rounded-xl hover:border-emerald-500 transition-all group">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <p class="font-bold text-xs text-slate-900 dark:text-white">Lịch hẹn học nhóm</p>
                    <p class="text-[10px] text-slate-400">Lên lịch học & Nhắc hẹn</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors"></i>
        </button>

        <!-- Nút Mini-game -->
        <button type="button" onclick="openModal('modal-mini-games')" class="w-full flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 rounded-xl hover:border-indigo-500 transition-all group">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i data-lucide="gamepad-2" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <p class="font-bold text-xs text-slate-900 dark:text-white">Mini-game giải trí</p>
                    <p class="text-[10px] text-slate-400">Xúc xắc, Oẳn tù tì...</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
        </button>

        <!-- Media tóm tắt đã gửi -->
        @php
            $mediaMessages = $activeConversation->messages->whereIn('type', ['image', 'document'])->take(6);
        @endphp
        @if($mediaMessages->isNotEmpty())
            <div class="pt-2">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-2">Ảnh & Tài liệu gần đây</p>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach($mediaMessages as $mMsg)
                        @if($mMsg->type === 'image')
                            @php
                                $imgSrc = $mMsg->file_url ?: ($mMsg->file_path ? asset('storage/' . $mMsg->file_path) : '');
                            @endphp
                            <div onclick="openLightbox('{{ $imgSrc }}')" class="aspect-square rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-800 cursor-pointer border border-slate-200/60 dark:border-slate-700/60 hover:opacity-80 transition-opacity">
                                <img src="{{ $imgSrc }}" alt="Ảnh" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div onclick="openDocumentViewer('{{ $mMsg->file_url ?: ($mMsg->file_path ? asset('storage/' . $mMsg->file_path) : '') }}', '{{ addslashes($mMsg->metadata['file_name'] ?? 'Tài liệu') }}', '{{ $mMsg->metadata['file_type'] ?? 'file' }}')" class="aspect-square rounded-lg p-1.5 bg-sky-50 dark:bg-sky-950/40 border border-sky-100 dark:border-sky-900/40 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-sky-100 transition-colors">
                                <i data-lucide="file-text" class="w-4 h-4 text-sky-500 mb-1"></i>
                                <span class="text-[9px] text-slate-600 dark:text-slate-300 truncate max-w-full font-medium">{{ $mMsg->metadata['file_name'] ?? 'Tài liệu' }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Phân khu 5: Cài đặt an toàn & Quản trị -->
    <div class="p-4 space-y-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Cài đặt an toàn</p>
        @if($isGroup)
            <button type="button" onclick="openGroupMembersModal(); switchGroupModalTab('settings');" class="w-full flex items-center gap-2.5 p-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="shield-check" class="w-4 h-4 text-indigo-500"></i>
                <span>Cài đặt quyền nhóm</span>
            </button>
            <button type="button" onclick="handleLeaveGroupClick()" class="w-full flex items-center gap-2.5 p-2.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Rời khỏi nhóm</span>
            </button>
        @else
            <button type="button" onclick="openPartnerProfileModal({{ $otherUser?->id ?? 0 }})" class="w-full flex items-center gap-2.5 p-2.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                <span>Chặn người dùng này</span>
            </button>
        @endif
    </div>
</aside>
