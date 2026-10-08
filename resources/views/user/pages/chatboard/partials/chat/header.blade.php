<!-- Header Chat -->
<div id="chat-header-main" 
     class="relative border-b border-slate-200 dark:border-slate-800 shrink-0"
     data-conversation-id="{{ $activeConversation->id }}"
     data-is-pinned="{{ ($currentParticipant?->is_pinned ?? false) ? '1' : '0' }}"
     data-is-muted="{{ ($currentParticipant && $currentParticipant->isMuted()) ? '1' : '0' }}"
     data-nickname="{{ $currentParticipant?->nickname ?? '' }}"
     data-original-name="{{ $activeConversation->is_group ? $activeConversation->name : (($activeConversation->participants->where('user_id', '!=', Auth::id())->first()->user?->name) ?? 'Nguoi dung') }}">
    <div class="h-16 flex items-center justify-between px-6">
        <div class="flex items-center gap-3">
            @php
                $isGroup = $activeConversation->is_group;
                $customNickname = $currentParticipant?->nickname;
                if ($isGroup) {
                    $originalName = $activeConversation->name;
                    $chatName = $customNickname ?: $originalName;
                } else {
                    $otherUser = $activeConversation->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
                    $originalName = $otherUser ? $otherUser->name : 'Nguoi dung';
                    $chatName = $customNickname ?: $originalName;
                }
                $isPinned = $currentParticipant?->is_pinned ?? false;
                $isMuted = $currentParticipant ? $currentParticipant->isMuted() : false;
            @endphp
            @if($isGroup)
                <div onclick="openGroupMembersModal()" class="flex items-center gap-3 cursor-pointer group" title="Xem thông tin & quản lý nhóm">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 flex items-center justify-center font-bold overflow-hidden transition-transform group-hover:scale-105">
                        @if($activeConversation->avatar_url)
                            <img src="{{ $activeConversation->avatar_url }}" alt="{{ $chatName }}" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="users" class="w-5 h-5"></i>
                        @endif
                    </div>
                    <div>
                        <h2 id="chat-header-group-title" class="font-extrabold text-slate-900 dark:text-white group-hover:text-indigo-500 transition-colors flex items-center gap-1.5">
                            <span id="chat-header-name-text">{{ $chatName }}</span>
                            <span id="chat-header-original-badge" class="text-[10px] font-normal text-slate-400 {{ $customNickname ? '' : 'hidden' }}">({{ $originalName }})</span>
                        </h2>
                        <p id="chat-header-status" class="text-xs text-emerald-500 font-medium">Đang hoạt động</p>
                    </div>
                </div>
            @else
                <div onclick="openPartnerProfileModal({{ $otherUser?->id ?? 0 }})" class="flex items-center gap-3 cursor-pointer group" title="Xem thông tin người dùng">
                    <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold overflow-hidden transition-transform group-hover:scale-105">
                        @if(isset($otherUser) && $otherUser && $otherUser->avatar_url)
                            <img src="{{ $otherUser->avatar_url }}" alt="{{ $chatName }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($chatName, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h2 id="chat-header-user-title" class="font-extrabold text-slate-900 dark:text-white group-hover:text-sky-500 transition-colors flex items-center gap-1.5">
                            <span id="chat-header-name-text">{{ $chatName }}</span>
                            <span id="chat-header-original-badge" class="text-[10px] font-normal text-slate-400 {{ $customNickname ? '' : 'hidden' }}">({{ $originalName }})</span>
                        </h2>
                        <p id="chat-header-status" class="text-xs text-emerald-500 font-medium">Đang hoạt động</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Cac nut hanh dong tren Header: Goi thoai, Goi video, Phong hoc nhom, Tim kiem, Ghim, Thong bao, Biet danh -->
        <div class="flex items-center gap-1.5">
            @if($isGroup)
                <!-- 1. Goi thoai nhom (Do chuong ca nhom) -->
                <button type="button" onclick="startCall('voice', null, 'call')" class="p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Gọi thoại nhóm (Đổ chuông)">
                    <i data-lucide="phone" class="w-5 h-5"></i>
                </button>
                <!-- 2. Goi video nhom (Do chuong ca nhom) -->
                <button type="button" onclick="startCall('video', null, 'call')" class="p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Gọi video nhóm (Đổ chuông)">
                    <i data-lucide="video" class="w-5 h-5"></i>
                </button>
                <!-- 3. Phong hoc truc tuyen (Mo qua lobby, phat banner, giơ tay, chu phong) -->
                <button type="button" onclick="openMeetingLobby('video')" class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Phòng học & Họp trực tuyến">
                    <i data-lucide="presentation" class="w-5 h-5"></i>
                </button>
                <!-- 4. Quan ly thanh vien nhom hoc tap -->
                <button type="button" onclick="openGroupMembersModal()" class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Thành viên nhóm">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </button>
            @else
                <!-- Cuoc goi 1-1 -->
                <button type="button" id="btn-header-call-voice" onclick="startCall('voice', null, 'call')" class="p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Gọi thoại">
                    <i data-lucide="phone" class="w-5 h-5"></i>
                </button>
                <button type="button" id="btn-header-call-video" onclick="startCall('video', null, 'call')" class="p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Gọi video">
                    <i data-lucide="video" class="w-5 h-5"></i>
                </button>
                <!-- 3. Thong tin doi phuong -->
                <button type="button" onclick="openPartnerProfileModal({{ $otherUser?->id ?? 0 }})" class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Thông tin người dùng">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </button>
            @endif

            <!-- 5. Tim kiem tin nhan -->
            <button type="button" onclick="toggleChatSearch()" class="p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Tìm kiếm tin nhắn">
                <i data-lucide="search" class="w-5 h-5"></i>
            </button>

            <!-- 6. Ghim cuoc tro chuyen (Pin) -->
            <button type="button" 
                    id="btn-header-pin-chat" 
                    onclick="handleTogglePinChat()" 
                    class="p-2 {{ $isPinned ? 'text-amber-500 bg-amber-50 dark:bg-amber-950/30' : 'text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800' }} rounded-xl transition-all" 
                    title="{{ $isPinned ? 'Bỏ ghim cuộc trò chuyện' : 'Ghim cuộc trò chuyện lên đầu' }}">
                <i data-lucide="pin" class="w-5 h-5 {{ $isPinned ? 'fill-amber-500' : '' }}"></i>
            </button>

            <!-- 7. Tat / Bat thong bao (Mute) -->
            <div class="relative" id="header-mute-container">
                <button type="button" 
                        id="btn-header-mute-chat" 
                        onclick="toggleMuteDropdown(event)" 
                        class="p-2 {{ $isMuted ? 'text-rose-500 bg-rose-50 dark:bg-rose-950/30' : 'text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800' }} rounded-xl transition-all" 
                        title="{{ $isMuted ? 'Đang tắt thông báo (Nhấn để tùy chỉnh)' : 'Tắt thông báo cuộc trò chuyện' }}">
                    <i data-lucide="{{ $isMuted ? 'bell-off' : 'bell' }}" class="w-5 h-5"></i>
                </button>
                <div id="header-mute-dropdown" class="hidden absolute right-0 mt-2 w-52 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl py-1.5 z-50 text-xs">
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

            <!-- 8. Dat biet danh cuoc tro chuyen -->
            <button type="button" 
                    id="btn-header-nickname" 
                    onclick="openChangeNicknameModal()" 
                    class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" 
                    title="Đặt biệt danh">
                <i data-lucide="tag" class="w-5 h-5"></i>
            </button>
        </div>
    </div>


    <!-- Thanh Banner Phong hoc nhom dang mo (Active Group Meeting Banner) -->
    @php
        $hasActiveMeeting = $isGroup && isset($activeMeeting) && in_array($activeMeeting->status, ['ringing', 'ongoing']);
    @endphp
    <div id="active-meeting-banner" class="{{ $hasActiveMeeting ? 'flex' : 'hidden' }} items-center justify-between px-6 py-2.5 bg-emerald-500/10 dark:bg-emerald-950/40 border-t border-emerald-500/20 text-xs transition-all z-20" data-room-code="{{ $activeMeeting?->room_code ?? '' }}" data-call-type="{{ $activeMeeting?->type ?? 'video' }}">
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                <i data-lucide="video" class="w-4 h-4 animate-pulse"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 font-bold text-emerald-900 dark:text-emerald-200 text-xs">
                    <span>Phòng học nhóm đang diễn ra</span>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-emerald-700/90 dark:text-emerald-300/90 mt-0.5">
                    <span id="active-meeting-host-desc">Chủ phòng: {{ $activeMeeting?->host?->name ?? 'Bạn học' }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 ml-3">
            <button type="button" onclick="openMeetingLobby('video')" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5">
                <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                <span>Tham gia phòng</span>
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
                        @elseif($pinnedMessage->type === 'event')
                            [Lịch hẹn]: {{ $pinnedMessage->body }}
                        @elseif($pinnedMessage->type === 'document')
                            [Tài liệu]: {{ $pinnedMessage->metadata['file_name'] ?? $pinnedMessage->body }}
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

    <!-- Thanh Banner Nhac hen sap toi (Upcoming Reminder Banner Zalo-style) -->
    @php
        $userJoinedUpcoming = false;
        if ($upcomingEvent ?? false) {
            $metaParticipants = $upcomingEvent->metadata['participants'] ?? [];
            $userJoinedUpcoming = isset($metaParticipants[Auth::id()]) || ($upcomingEvent->user_id === Auth::id());
        }
    @endphp
    <div id="upcoming-reminder-banner" class="{{ ($upcomingEvent ?? false) ? 'flex' : 'hidden' }} items-center justify-between px-6 py-2.5 bg-amber-500/10 dark:bg-amber-950/30 border-t border-amber-200/60 dark:border-amber-800/40 text-xs transition-all z-20" data-event-id="{{ $upcomingEvent?->id ?? 0 }}" data-remind-at="{{ $upcomingEvent?->metadata['remind_at'] ?? '' }}" data-remind-before="{{ $upcomingEvent?->metadata['remind_before'] ?? 15 }}" data-location="{{ $upcomingEvent?->metadata['location'] ?? '' }}" data-joined="{{ $userJoinedUpcoming ? '1' : '0' }}">
        <div class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer hover:opacity-90 transition-opacity" onclick="jumpToReminderEvent()">
            <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="bell-ring" class="w-4 h-4 animate-bounce"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 font-bold text-amber-900 dark:text-amber-200 text-xs">
                    <span>Sắp đến lịch hẹn:</span>
                    <span id="reminder-banner-title" class="truncate font-semibold">{{ $upcomingEvent?->metadata['title'] ?? ($upcomingEvent?->body ?? '') }}</span>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-amber-700/80 dark:text-amber-300/80 mt-0.5">
                    <span id="reminder-banner-time" class="font-semibold"></span>
                    <span>•</span>
                    <span id="reminder-banner-countdown" class="font-bold text-amber-600 dark:text-amber-400">Đang tính thời gian...</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 ml-3">
            @php
                $eventLoc = $upcomingEvent?->metadata['location'] ?? '';
                $isOnlineLink = str_starts_with($eventLoc, 'http://') || str_starts_with($eventLoc, 'https://');
            @endphp
            <a id="btn-reminder-join-link" href="{{ $isOnlineLink ? $eventLoc : '#' }}" target="_blank" rel="noopener noreferrer" class="{{ $isOnlineLink ? 'flex' : 'hidden' }} items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition-all" onclick="event.stopPropagation()">
                <i data-lucide="video" class="w-3.5 h-3.5"></i>
                <span>Vào phòng học</span>
            </a>
            <button type="button" onclick="jumpToReminderEvent()" class="px-2.5 py-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-800 dark:text-amber-200 font-semibold text-xs transition-colors">
                Xem tin
            </button>
            <button type="button" onclick="dismissReminderBanner(event)" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md transition-colors" title="Ẩn nhắc nhở này">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </div>
</div>
