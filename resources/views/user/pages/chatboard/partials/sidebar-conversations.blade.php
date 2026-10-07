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

    @if(isset($pendingRequests) && $pendingRequests->count() > 0)
    <div class="px-3 pb-1">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pb-1.5">Lời mời kết bạn ({{ $pendingRequests->count() }})</p>
        @foreach($pendingRequests as $req)
        <div class="flex items-center gap-2.5 p-2.5 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl mb-1.5">
            <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0 overflow-hidden">
                @if($req->sender->avatar_url)
                    <img src="{{ $req->sender->avatar_url }}" alt="{{ $req->sender->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($req->sender->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $req->sender->name }}</p>
                <p class="text-[10px] text-slate-500 truncate">{{ $req->sender->email }}</p>
            </div>
            <form method="POST" action="{{ route('app.friend.accept', $req->id) }}" class="shrink-0">
                @csrf
                <button type="submit" class="p-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors" title="Chấp nhận">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>
    @endif

    <div class="flex-1 overflow-y-auto p-2 space-y-1">
        @if(isset($conversations) && $conversations->isEmpty())
            <p class="text-xs text-slate-400 text-center py-4">Chưa có cuộc trò chuyện nào</p>
        @else
            @foreach($conversations ?? [] as $conv)
                @php
                    $isGroup = $conv->is_group;
                    if ($isGroup) {
                        $name = $conv->name;
                        $avatarChar = strtoupper(substr($name, 0, 1));
                    } else {
                        $otherUser = $conv->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
                        $name = $otherUser ? $otherUser->name : 'Người dùng';
                        $avatarChar = strtoupper(substr($name, 0, 1));
                    }
                    $isActive = isset($activeConversation) && $activeConversation->id === $conv->id;
                @endphp

                <a href="{{ route('app.chat-board.show', $conv->id) }}" 
                   class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ $isActive ? 'bg-sky-50 dark:bg-sky-900/30' : 'hover:bg-white dark:hover:bg-slate-800' }}">
                    
                    <div class="relative shrink-0">
                        @if($isGroup)
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 flex items-center justify-center font-bold text-sm">
                                <i data-lucide="users" class="w-5 h-5"></i>
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
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ $isGroup ? 'Nhóm học tập' : 'Trò chuyện cá nhân' }}
                        </p>
                    </div>
                </a>
            @endforeach
        @endif
    </div>
</section>
