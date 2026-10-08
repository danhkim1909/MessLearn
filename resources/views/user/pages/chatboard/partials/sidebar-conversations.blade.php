<!-- CỘT 2: DANH SÁCH CUỘC TRÒ CHUYỆN -->
<section class="w-80 bg-slate-50 dark:bg-slate-900/50 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0">
    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <h2 class="font-extrabold text-lg text-slate-900 dark:text-white">Tin nhắn</h2>
        <div class="flex items-center gap-1.5">
            <button onclick="openModal('modal-add-friend')" class="p-2 text-slate-400 hover:text-sky-500 transition-colors" title="Thêm bạn">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
            </button>
            <button onclick="openModal('modal-create-group')" class="p-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg shadow-md shadow-sky-500/20 transition-all" title="Tạo nhóm">
                <i data-lucide="plus" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    
    <div class="p-3">
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" placeholder="Tìm hội thoại..." class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-sky-500 transition-colors">
        </div>
    </div>

    <div id="sidebar-pending-requests-container" class="px-3 pb-1 {{ (isset($pendingRequests) && $pendingRequests->count() > 0) ? '' : 'hidden' }}">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pb-1.5 flex items-center justify-between">
            <span>Lời mời kết bạn</span>
            <span id="sidebar-pending-requests-count" class="bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ isset($pendingRequests) ? $pendingRequests->count() : 0 }}</span>
        </p>
        <div id="sidebar-pending-requests-list" class="space-y-1.5">
            @foreach($pendingRequests ?? [] as $req)
            <div id="pending-request-item-{{ $req->id }}" class="flex items-center gap-2.5 p-2.5 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0 overflow-hidden">
                    @if($req->sender && $req->sender->avatar_url)
                        <img src="{{ $req->sender->avatar_url }}" alt="{{ $req->sender->name }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($req->sender?->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $req->sender?->name ?? 'Người dùng' }}</p>
                    <p class="text-[10px] text-slate-500 truncate">{{ $req->sender?->email ?? '' }}</p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" onclick="acceptFriendFromSidebar({{ $req->id }})" class="p-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors" title="Chấp nhận">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="rejectFriendFromSidebar({{ $req->id }})" class="p-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-rose-500 hover:text-white text-slate-600 dark:text-slate-300 rounded-lg transition-colors" title="Từ chối">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-2 space-y-1">
        @if(isset($conversations) && $conversations->isEmpty())
            <p class="text-xs text-slate-400 text-center py-4">Chưa có cuộc trò chuyện nào</p>
        @else
            @foreach($conversations ?? [] as $conv)
                @php
                    $isGroup = $conv->is_group;
                    $myParticipant = $conv->participants->where('user_id', Auth::id())->first();
                    $isPinned = $myParticipant?->is_pinned ?? false;
                    $isMuted = $myParticipant ? $myParticipant->isMuted() : false;
                    $customNickname = $myParticipant?->nickname;

                    if ($isGroup) {
                        $name = $customNickname ?: $conv->name;
                        $avatarChar = strtoupper(substr($name, 0, 1));
                    } else {
                        $otherUser = $conv->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
                        $name = $customNickname ?: ($otherUser ? $otherUser->name : 'Người dùng');
                        $avatarChar = strtoupper(substr($name, 0, 1));
                    }
                    $isActive = isset($activeConversation) && $activeConversation->id === $conv->id;
                    $unreadCount = $conv->unread_count ?? 0;
                    $lastMessage = $conv->messages->first();

                    $lastMsgText = '';
                    if ($lastMessage) {
                        $senderPrefix = $lastMessage->user_id === Auth::id() ? 'Bạn: ' : '';
                        if ($lastMessage->type === 'image') {
                            $lastMsgText = $senderPrefix . '[Hình ảnh]';
                        } elseif ($lastMessage->type === 'audio') {
                            $lastMsgText = $senderPrefix . '[Tin nhắn thoại]';
                        } elseif ($lastMessage->type === 'quiz') {
                            $lastMsgText = $senderPrefix . '[Bài kiểm tra]';
                        } elseif ($lastMessage->type === 'event') {
                            $lastMsgText = $senderPrefix . '[Lịch hẹn]';
                        } elseif ($lastMessage->type === 'document') {
                            $lastMsgText = $senderPrefix . '[Tài liệu]';
                        } elseif ($lastMessage->type === 'game_dice') {
                            $lastMsgText = $senderPrefix . '[Tung xúc xắc]';
                        } elseif ($lastMessage->type === 'game_rps') {
                            $lastMsgText = $senderPrefix . '[Oẳn tù tì]';
                        } elseif ($lastMessage->type === 'recalled') {
                            $lastMsgText = $senderPrefix . '[Tin nhắn đã gỡ]';
                        } else {
                            $lastMsgText = $senderPrefix . $lastMessage->body;
                        }
                    } else {
                        $lastMsgText = $isGroup ? 'Nhóm học tập' : 'Bắt đầu trò chuyện';
                    }
                @endphp

                <a href="{{ route('app.chat-board.show', $conv->id) }}" 
                   id="sidebar-conv-{{ $conv->id }}"
                   class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ $isActive ? 'bg-sky-50 dark:bg-sky-900/30' : 'hover:bg-white dark:hover:bg-slate-800' }}">
                    
                    <div class="relative shrink-0">
                        @if($isGroup)
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 flex items-center justify-center font-bold text-sm overflow-hidden">
                                @if($conv->avatar_url)
                                    <img src="{{ $conv->avatar_url }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="users" class="w-5 h-5"></i>
                                @endif
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-sm overflow-hidden">
                                @if($otherUser && $otherUser->avatar_url)
                                    <img src="{{ $otherUser->avatar_url }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                @else
                                    {{ $avatarChar }}
                                @endif
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $name }}</h3>
                            <div class="flex items-center gap-1 shrink-0">
                                @if($isMuted)
                                    <i data-lucide="bell-off" class="w-3.5 h-3.5 text-slate-400" title="Đang tắt thông báo"></i>
                                @endif
                                @if($isPinned)
                                    <i data-lucide="pin" class="w-3.5 h-3.5 text-amber-500 fill-amber-500" title="Đã ghim"></i>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p id="sidebar-last-msg-{{ $conv->id }}" class="text-xs text-slate-500 dark:text-slate-400 truncate flex-1 {{ ($unreadCount > 0 && !$isActive) ? 'font-bold text-slate-800 dark:text-slate-200' : '' }}">
                                {{ $lastMsgText }}
                            </p>
                            <span id="unread-badge-{{ $conv->id }}" class="shrink-0 px-1.5 py-0.5 min-w-5 h-5 flex items-center justify-center rounded-full text-[10px] font-extrabold bg-sky-500 text-white shadow-xs {{ ($unreadCount > 0 && !$isActive) ? '' : 'hidden' }}">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endif
    </div>
</section>
