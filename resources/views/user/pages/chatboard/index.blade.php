@extends('user.layouts.app')
@section('title', 'MessLearn - Cửa sổ trò chuyện & Học tập')

@section('content')
<div class="flex-1 flex overflow-hidden h-[calc(100vh-3.5rem)]">
    <!-- CỘT 1: SIDEBAR ĐIỀU HƯỚNG HẸP -->
    <aside class="w-16 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col items-center py-4 justify-between shrink-0">
        <div class="flex flex-col items-center gap-4 w-full">
            <div class="relative group cursor-pointer" title="{{ Auth::user()->name }}">
                <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-sky-500/30">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
            </div>
            <hr class="w-8 border-slate-200 dark:border-slate-800">
            <nav class="flex flex-col gap-2 w-full px-2">
                <a href="{{ route('app.chat-board') }}" class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center transition-all" title="Trò chuyện">
                    <i data-lucide="message-square" class="w-5 h-5"></i>
                </a>
                <a href="#" class="p-2.5 rounded-xl text-slate-400 hover:text-sky-500 hover:bg-sky-50 dark:hover:bg-sky-900/30 flex items-center justify-center transition-all" title="Thêm bạn bè" onclick="openModal('modal-add-friend')">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </a>
            </nav>
        </div>
        <div class="flex flex-col gap-2 w-full px-2">
            <form action="{{ route('auth.logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full p-2.5 rounded-xl text-rose-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 flex items-center justify-center transition-all" title="Đăng xuất">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </button>
            </form>
        </div>
    </aside>

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
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr($req->sender->name, 0, 1)) }}
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
                                <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-sm">
                                    {{ $avatarChar }}
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

    <!-- CỘT 3: CỬA SỔ CHAT CHÍNH -->
    <main class="flex-1 bg-white dark:bg-slate-900 flex flex-col min-w-0 border-r border-slate-200 dark:border-slate-800">
        @if(isset($activeConversation))
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

            <!-- Nội dung Chat -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-messages-container">
                @if($activeConversation->messages->isEmpty())
                    <div class="h-full flex items-center justify-center text-sm text-slate-400">
                        Chưa có tin nhắn nào. Bắt đầu trò chuyện!
                    </div>
                @else
                    @foreach($activeConversation->messages as $message)
                        @php
                            $isMine = $message->user_id === Auth::id();
                        @endphp
                        <div id="msg-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                            <div class="flex gap-2 max-w-[75%] {{ $isMine ? 'flex-row-reverse' : 'flex-row' }}">
                                @if(!$isMine)
                                    <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">
                                        {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    @if(!$isMine)
                                        <div class="flex items-baseline gap-2 mb-1 ml-1">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $message->user->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $message->created_at->format('H:i') }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-baseline gap-2 mb-1 mr-1 justify-end">
                                            <span class="text-[10px] text-slate-400">{{ $message->created_at->format('H:i') }}</span>
                                        </div>
                                    @endif
                                    <div class="{{ $isMine ? 'bg-sky-500 text-white rounded-tr-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-sm' }} px-4 py-2.5 rounded-2xl text-xs max-w-md relative group">
                                        @if($message->replyTo)
                                            <div onclick="scrollToMessage({{ $message->reply_to_id }})" class="cursor-pointer hover:opacity-100 transition-all mb-2 p-2 rounded-xl {{ $isMine ? 'bg-black/10' : 'bg-black/5 dark:bg-white/5' }} border-l-2 {{ $isMine ? 'border-white/50' : 'border-sky-500' }} text-[11px] opacity-80">
                                                <div class="font-bold mb-0.5">{{ $message->replyTo->user->name }}</div>
                                                <div class="truncate">{{ $message->replyTo->type === 'quiz' ? 'Bài kiểm tra: ' . $message->replyTo->body : $message->replyTo->body }}</div>
                                            </div>
                                        @endif
                                        
                                        <!-- Nút Reply -->
                                        <button onclick="prepareReply({{ $message->id }}, '{{ addslashes($message->user->name) }}', '{{ addslashes(str_replace(['\r', '\n'], ' ', \Illuminate\Support\Str::limit($message->type === 'quiz' ? 'Bài kiểm tra: '.$message->body : $message->body, 50))) }}')" class="absolute {{ $isMine ? 'right-full mr-2' : 'left-full ml-2' }} top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10" title="Trả lời">
                                            <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                        </button>
                                        @if($message->type === 'quiz')
                                            <div class="flex flex-col gap-2 {{ $isMine ? 'text-white' : 'text-slate-800 dark:text-slate-200' }}">
                                                <div class="flex items-center gap-2 font-bold mb-1">
                                                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                                                    Bài kiểm tra
                                                </div>
                                                <p class="font-medium text-sm">{{ $message->body }}</p>
                                                @php
                                                    $hasSubmitted = $message->quiz && $message->quiz->submissions->where('user_id', Auth::id())->count() > 0;
                                                @endphp
                                                <div class="flex gap-2 mt-2">
                                                    @if($hasSubmitted)
                                                        <button type="button" disabled class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed">
                                                            Đã làm bài
                                                        </button>
                                                    @else
                                                        <button type="button" onclick="openQuizRunner({{ $message->quiz_id }})" class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all {{ $isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white' }}">
                                                            Bắt đầu làm bài
                                                        </button>
                                                    @endif
                                                    <button type="button" onclick="openQuizLeaderboard({{ $message->quiz_id }})" class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all bg-amber-500 hover:bg-amber-600 text-white">
                                                        Xem điểm
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            {!! nl2br(e($message->body)) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Khung nhập Chat -->
            <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 shrink-0 flex flex-col">
                <div id="reply-preview-container" class="hidden mb-3 mx-12 p-3 bg-slate-50 dark:bg-slate-800/80 border-l-4 border-sky-500 rounded-xl flex items-center justify-between">
                    <div class="text-xs min-w-0 flex-1">
                        <div class="font-bold text-slate-700 dark:text-slate-300 mb-0.5">Đang trả lời: <span id="reply-to-name"></span></div>
                        <div class="text-slate-500 dark:text-slate-400 truncate" id="reply-to-text"></div>
                    </div>
                    <button type="button" onclick="cancelReply()" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors ml-3 shrink-0">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form id="chat-form" class="flex items-end gap-2" onsubmit="sendChatMessage(event)">
                    <input type="hidden" id="reply-to-id" value="">
                    <button type="button" class="p-3 text-slate-400 hover:text-sky-500 transition-colors">
                        <i data-lucide="paperclip" class="w-5 h-5"></i>
                    </button>
                    <div class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-1 relative">
                        <textarea id="chat-input" rows="1" class="w-full bg-transparent px-3 py-2 text-sm focus:outline-none dark:text-white resize-none max-h-32" placeholder="Nhập tin nhắn..." onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendChatMessage(event); }"></textarea>
                    </div>
                    <button type="submit" class="p-3 bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow-md shadow-sky-500/20 transition-all flex items-center justify-center">
                        <i data-lucide="send" class="w-5 h-5 ml-1"></i>
                    </button>
                </form>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="message-square" class="w-10 h-10"></i>
                </div>
                <p class="text-sm">Chọn một cuộc trò chuyện để bắt đầu</p>
            </div>
        @endif
    </main>

    <!-- CỘT 4: RIGHT SIDEBAR - CÔNG CỤ HỌC TẬP -->
    @if(isset($activeConversation))
    <aside class="w-80 bg-slate-50 dark:bg-slate-900/50 hidden lg:flex flex-col shrink-0">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Không gian học tập</h2>
        </div>
        <div class="p-4 space-y-3 flex-1 overflow-y-auto">

            {{-- Tạo Quiz - đã có --}}
            <button onclick="openModal('modal-create-quiz')" class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-sky-500 hover:shadow-md hover:shadow-sky-500/10 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="pen-tool" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Tạo Quiz</h4>
                        <p class="text-[10px] text-slate-500">Bài kiểm tra & Khảo sát</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-sky-500 transition-colors"></i>
            </button>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1 pt-1">Sắp có</p>

            {{-- Bảng xếp hạng - placeholder --}}
            <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-500 flex items-center justify-center">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Bảng xếp hạng</h4>
                        <p class="text-[10px] text-slate-500">Xếp hạng tuần theo điểm</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
            </button>

            {{-- Voice Note - placeholder --}}
            <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-500 flex items-center justify-center">
                        <i data-lucide="mic" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Ghi âm Voice Note</h4>
                        <p class="text-[10px] text-slate-500">Gửi ghi âm thoại vào chat</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
            </button>

            {{-- Vẽ lên ảnh - placeholder --}}
            <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-500 flex items-center justify-center">
                        <i data-lucide="image" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Vẽ lên ảnh</h4>
                        <p class="text-[10px] text-slate-500">Chú thích ảnh bằng Canvas</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
            </button>

            {{-- Lịch hẹn nhóm - placeholder --}}
            <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Lịch hẹn nhóm</h4>
                        <p class="text-[10px] text-slate-500">Đặt lịch học & sự kiện</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
            </button>

            {{-- Mini-game - placeholder --}}
            <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 flex items-center justify-center">
                        <i data-lucide="gamepad-2" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Mini-game</h4>
                        <p class="text-[10px] text-slate-500">Xúc xắc, kéo búa bao...</p>
                    </div>
                </div>
                <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
            </button>

        </div>
    </aside>
    @endif
</div>

<!-- MODAL: TẠO FORM CUSTOM -->
<div id="modal-create-quiz" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full h-[90vh] max-w-4xl rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 p-6 shrink-0">
            <div>
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Tạo Bài Kiểm Tra / Khảo Sát</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Thiết kế form nhanh chóng chuyên biệt cho Học tập</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="submitCustomForm()" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded-xl shadow-md shadow-sky-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Lưu & Đăng vào Nhóm
                </button>
                <button type="button" onclick="closeModal('modal-create-quiz')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/30">
            <div class="max-w-2xl mx-auto space-y-6">
                <div class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm space-y-4">
                    <div>
                        <input type="text" id="quiz-title" placeholder="Tiêu đề Form (Bắt buộc)" class="w-full bg-transparent text-xl font-bold text-slate-900 dark:text-white border-b-2 border-slate-200 dark:border-slate-700 focus:border-sky-500 focus:outline-none py-2 transition-colors" required>
                    </div>
                    <div>
                        <textarea id="quiz-desc" rows="2" placeholder="Mô tả thêm (Không bắt buộc)" class="w-full bg-transparent text-sm text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700 focus:border-sky-500 focus:outline-none py-2 resize-none transition-colors"></textarea>
                    </div>
                </div>
                <div id="quiz-builder-container" class="space-y-4">
                </div>
                <div class="flex justify-center pt-2 pb-8">
                    <button type="button" onclick="addQuizQuestion()" class="px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-sky-500 hover:text-sky-500 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        Thêm Câu Hỏi Mới
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: TÌM KẾT BẠN BẰNG EMAIL -->
<div id="modal-add-friend" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Kết bạn bằng Email</h3>
            <button type="button" onclick="closeModal('modal-add-friend')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nhập Email người dùng</label>
            <div class="flex gap-2">
                <input type="email" id="add-friend-email" class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500 transition-colors dark:text-white" placeholder="vd: loc@gmail.com">
                <button type="button" onclick="sendFriendRequest()" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Gửi
                </button>
            </div>
            <p id="add-friend-msg" class="text-xs mt-2 hidden"></p>
        </div>
    </div>
</div>

<!-- MODAL 2: TẠO NHÓM MỚI -->
<div id="modal-create-group" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Tạo Nhóm Học Tập</h3>
            <button type="button" onclick="closeModal('modal-create-group')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tên nhóm</label>
            <input type="text" id="group-name" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors dark:text-white mb-4" placeholder="VD: Nhóm ôn thi Toán">
            
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Chọn bạn bè vào nhóm</label>
            <div class="max-h-40 overflow-y-auto space-y-2 mb-4 border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50">
                @if(isset($friends) && $friends->count() > 0)
                    @foreach($friends as $friend)
                        <label class="flex items-center gap-3 p-2 hover:bg-white dark:hover:bg-slate-800 rounded-lg cursor-pointer transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                            <input type="checkbox" name="group_members[]" value="{{ $friend->id }}" class="w-4 h-4 text-indigo-500 border-slate-300 rounded focus:ring-indigo-500">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($friend->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $friend->name }}</span>
                        </label>
                    @endforeach
                @else
                    <p class="text-xs text-slate-400 text-center py-2">Bạn chưa kết bạn với ai.</p>
                @endif
            </div>

            <button type="button" onclick="createGroup()" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 transition-all">
                Tạo Nhóm
            </button>
            <p id="create-group-msg" class="text-xs mt-2 hidden text-center"></p>
        </div>
    </div>
</div>

<!-- MODAL: LÀM BÀI TRẮC NGHIỆM / KHẢO SÁT -->
<div id="modal-take-quiz" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full h-[90vh] max-w-4xl rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 p-6 shrink-0">
            <div>
                <h3 id="quiz-run-title" class="font-bold text-lg text-slate-900 dark:text-white">Đang tải...</h3>
                <p id="quiz-run-desc" class="text-xs text-slate-500 dark:text-slate-400"></p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-submit-quiz" onclick="submitQuiz()" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Nộp Bài
                </button>
                <button type="button" onclick="closeModal('modal-take-quiz')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/30">
            <div class="max-w-2xl mx-auto space-y-6" id="quiz-run-container">
                <div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div></div>
            </div>
            
            <div id="quiz-result-container" class="max-w-2xl mx-auto mt-6 hidden">
                <div class="p-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-center">
                    <h4 class="text-emerald-600 dark:text-emerald-400 font-bold text-lg mb-2">Đã nộp bài thành công!</h4>
                    <p class="text-slate-600 dark:text-slate-300 text-sm">Điểm số của bạn: <span id="quiz-score" class="font-bold text-xl text-emerald-600 dark:text-emerald-400"></span></p>
                    <button type="button" onclick="closeModal('modal-take-quiz')" class="mt-4 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- MODAL: BẢNG XẾP HẠNG & CHI TIẾT -->
<div id="modal-quiz-leaderboard" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full h-[90vh] max-w-4xl rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 p-6 shrink-0">
            <div>
                <h3 id="leaderboard-title" class="font-bold text-lg text-slate-900 dark:text-white">Bảng Xếp Hạng</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kết quả làm bài của các thành viên</p>
            </div>
            <button type="button" onclick="closeModal('modal-quiz-leaderboard')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/30">
            <div id="leaderboard-container" class="max-w-3xl mx-auto space-y-4">
                <div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        if (id === 'modal-create-quiz') {
            const container = document.getElementById('quiz-builder-container');
            if (container.children.length === 0) {
                addQuizQuestion();
            }
        }
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    async function sendFriendRequest() {
        const email = document.getElementById('add-friend-email').value;
        const msgEl = document.getElementById('add-friend-msg');
        
        if (!email) {
            msgEl.innerText = 'Vui lòng nhập email!';
            msgEl.className = 'text-xs mt-2 text-rose-500 block';
            return;
        }

        try {
            const res = await fetch('{{ route('app.friend.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            });

            const data = await res.json();

            if (res.ok) {
                msgEl.innerText = 'Gửi kết bạn thành công!';
                msgEl.className = 'text-xs mt-2 text-emerald-500 block';
                document.getElementById('add-friend-email').value = '';
                Toastify({text: "Đã gửi yêu cầu kết bạn!", style: {background: "#10b981"}}).showToast();
                setTimeout(() => closeModal('modal-add-friend'), 1500);
            } else {
                msgEl.innerText = data.message || 'Lỗi gửi yêu cầu';
                msgEl.className = 'text-xs mt-2 text-rose-500 block';
            }
        } catch (err) {
            msgEl.innerText = 'Lỗi kết nối mạng';
            msgEl.className = 'text-xs mt-2 text-rose-500 block';
        }
    }

    async function createGroup() {
        const name = document.getElementById('group-name').value;
        const memberCheckboxes = document.querySelectorAll('input[name="group_members[]"]:checked');
        const userIds = Array.from(memberCheckboxes).map(cb => cb.value);
        const msgEl = document.getElementById('create-group-msg');

        if (!name) {
            msgEl.innerText = 'Vui lòng nhập tên nhóm!';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
            return;
        }

        if (userIds.length === 0) {
            msgEl.innerText = 'Vui lòng chọn ít nhất 1 thành viên!';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
            return;
        }

        try {
            const res = await fetch('{{ route('app.conversation.store-group') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: name, user_ids: userIds })
            });

            const data = await res.json();

            if (res.ok) {
                Toastify({text: "Tạo nhóm thành công!", style: {background: "#10b981"}}).showToast();
                setTimeout(() => window.location.href = '{{ url('app/c') }}/' + data.conversation.id, 1000);
            } else {
                msgEl.innerText = data.message || 'Lỗi tạo nhóm';
                msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
            }
        } catch (err) {
            msgEl.innerText = 'Lỗi kết nối mạng';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
        }
    }

    @if(isset($activeConversation))
        function appendMessageToChat(message) {
            const chatContainer = document.getElementById('chat-messages-container');
            if(!chatContainer) return;
            const isMine = message.user_id === {{ Auth::id() }};
            const avatarChar = message.user.name.charAt(0).toUpperCase();

            let innerContent = '';
            if (message.reply_to) {
                innerContent += `
                    <div onclick="scrollToMessage(${message.reply_to_id})" class="cursor-pointer hover:opacity-100 transition-all mb-2 p-2 rounded-xl ${isMine ? 'bg-black/10' : 'bg-black/5 dark:bg-white/5'} border-l-2 ${isMine ? 'border-white/50' : 'border-sky-500'} text-[11px] opacity-80">
                        <div class="font-bold mb-0.5">${message.reply_to.user ? message.reply_to.user.name : ''}</div>
                        <div class="truncate">${message.reply_to.type === 'quiz' ? 'Bài kiểm tra: ' + message.reply_to.body : message.reply_to.body}</div>
                    </div>
                `;
            }
            
            if (message.type === 'quiz') {
                innerContent += `
                    <div class="flex flex-col gap-2 ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-200'}">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <i data-lucide="help-circle" class="w-4 h-4"></i>
                            Bài kiểm tra
                        </div>
                        <p class="font-medium text-sm">${message.body}</p>
                        <div class="flex gap-2 mt-2">
                            <button type="button" onclick="openQuizRunner(${message.quiz_id || 0})" class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all ${isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white'}">
                                Bắt đầu làm bài
                            </button>
                            <button type="button" onclick="openQuizLeaderboard(${message.quiz_id || 0})" class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all bg-amber-500 hover:bg-amber-600 text-white">
                                Xem điểm
                            </button>
                        </div>
                    </div>
                `;
            } else {
                innerContent += (message.body || '').replace(/\n/g, "<br>");
            }

            const messageHtml = `
                <div id="msg-${message.id}" class="flex ${isMine ? 'justify-end' : 'justify-start'}">
                    <div class="flex gap-2 max-w-[75%] ${isMine ? 'flex-row-reverse' : 'flex-row'}">
                        ${!isMine ? `
                            <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">
                                ${avatarChar}
                            </div>
                        ` : ''}
                        <div>
                            ${!isMine ? `
                                <div class="flex items-baseline gap-2 mb-1 ml-1">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${message.user.name}</span>
                                    <span class="text-[10px] text-slate-400">Vừa xong</span>
                                </div>
                            ` : `
                                <div class="flex items-baseline gap-2 mb-1 mr-1 justify-end">
                                    <span class="text-[10px] text-slate-400">Vừa xong</span>
                                </div>
                            `}
                            <div class="${isMine ? 'bg-sky-500 text-white rounded-tr-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-sm'} px-4 py-2.5 rounded-2xl text-xs max-w-md relative group">
                                ${innerContent}
                                <button onclick="prepareReply(${message.id}, '${(message.user.name || '').replace(/'/g, '\\\'')}', '${(message.type === 'quiz' ? 'Bài kiểm tra: ' + message.body : message.body || '').replace(/'/g, '\\\'').replace(/\r\n|\n|\r/g, ' ').substring(0, 50)}')" class="absolute ${isMine ? 'right-full mr-2' : 'left-full ml-2'} top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10" title="Trả lời">
                                    <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            chatContainer.insertAdjacentHTML('beforeend', messageHtml);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            lucide.createIcons();
        }


        function scrollToMessage(id) {
            const el = document.getElementById('msg-' + id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const bubble = el.querySelector('.max-w-md');
                if (bubble) {
                    bubble.classList.add('ring-4', 'ring-amber-300', 'dark:ring-amber-500', 'transition-all');
                    setTimeout(() => {
                        bubble.classList.remove('ring-4', 'ring-amber-300', 'dark:ring-amber-500');
                    }, 1500);
                }
            }
        }

        function prepareReply(messageId, userName, text) {
            document.getElementById('reply-to-id').value = messageId;
            document.getElementById('reply-to-name').innerText = userName;
            document.getElementById('reply-to-text').innerText = text;
            document.getElementById('reply-preview-container').classList.remove('hidden');
            document.getElementById('chat-input').focus();
        }

        function cancelReply() {
            document.getElementById('reply-to-id').value = '';
            document.getElementById('reply-preview-container').classList.add('hidden');
        }
        
        async function sendChatMessage(e) {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const replyInput = document.getElementById('reply-to-id');
            const text = input.value.trim();
            const replyToId = replyInput ? replyInput.value : '';
            
            if (!text) return;

            input.value = '';
            input.style.height = 'auto';
            cancelReply();
            input.focus();
            
            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                const payload = { body: text };
                if (replyToId) {
                    payload.reply_to_id = replyToId;
                }

                const res = await fetch('{{ route('app.conversation.message.store', $activeConversation->id) }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    appendMessageToChat(data);
                } else {
                    Toastify({text: "Lỗi gửi tin nhắn", style: {background: "#f43f5e"}}).showToast();
                }
            } catch (err) {
                Toastify({text: "Lỗi kết nối", style: {background: "#f43f5e"}}).showToast();
            }
        }

        const chatContainer = document.getElementById('chat-messages-container');
        if(chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('conversation.{{ $activeConversation->id }}')
                    .listen('.MessageSent', (e) => {
                        appendMessageToChat(e.message);
                    });
            }
        });
    @endif

    let questionCount = 0;

    function renderQuestionHTML(qId, index) {
        return `
            <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl relative quiz-question-item shadow-sm mb-4 transition-all" data-id="${qId}">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-500 flex items-center justify-center font-extrabold text-xs">Câu ${index}</span>
                        <select class="q-type bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg px-3 py-1.5 focus:outline-none focus:border-sky-500 transition-all cursor-pointer" onchange="changeQuestionType('${qId}')">
                            <option value="radio">Trắc nghiệm (1 đáp án đúng)</option>
                            <option value="checkbox">Trắc nghiệm (Nhiều đáp án đúng)</option>
                            <option value="text">Tự luận ngắn (Khảo sát)</option>
                        </select>
                    </div>
                    <button type="button" onclick="this.closest('.quiz-question-item').remove()" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 p-1.5 rounded-lg transition-all" title="Xóa câu hỏi">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <input type="text" class="q-title w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:border-sky-500 focus:bg-white transition-all" placeholder="Nhập nội dung câu hỏi..." required>
                    </div>
                    
                    <div class="q-options-container space-y-2.5 ml-2" id="options_${qId}">
                    </div>

                    <button type="button" onclick="addOption('${qId}')" class="btn-add-opt text-[11px] font-bold text-sky-500 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/30 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 mt-2 transition-all">
                        <i data-lucide="plus" class="w-3 h-3"></i> Thêm đáp án
                    </button>
                </div>
            </div>
        `;
    }

    function renderOptionHTML(qId, optId, type) {
        const inputType = type === 'radio' ? 'radio' : 'checkbox';
        return `
            <div class="flex items-center gap-3 option-item group relative">
                <div class="relative flex items-center justify-center cursor-pointer" title="Đánh dấu đây là đáp án đúng">
                    <input type="${inputType}" name="correct_${qId}" value="${optId}" class="w-4 h-4 text-emerald-500 border-slate-300 focus:ring-emerald-500 cursor-pointer peer">
                </div>
                <input type="text" class="opt-text flex-1 px-3 py-2 bg-transparent border-b border-transparent group-hover:border-slate-200 dark:group-hover:border-slate-700 focus:border-sky-500 text-sm text-slate-700 dark:text-slate-300 focus:outline-none transition-all" placeholder="Nhập lựa chọn...">
                <button type="button" onclick="this.closest('.option-item').remove()" class="text-slate-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        `;
    }

    function addQuizQuestion() {
        questionCount++;
        const qId = `q_${Date.now()}_${questionCount}`;
        const container = document.getElementById('quiz-builder-container');
        
        container.insertAdjacentHTML('beforeend', renderQuestionHTML(qId, questionCount));
        
        addOption(qId);
        addOption(qId);
        
        lucide.createIcons();
    }

    function addOption(qId) {
        const qItem = document.querySelector(`.quiz-question-item[data-id="${qId}"]`);
        const type = qItem.querySelector('.q-type').value;
        const container = document.getElementById(`options_${qId}`);
        const optId = `opt_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
        
        container.insertAdjacentHTML('beforeend', renderOptionHTML(qId, optId, type));
        lucide.createIcons();
    }

    function changeQuestionType(qId) {
        const qItem = document.querySelector(`.quiz-question-item[data-id="${qId}"]`);
        const type = qItem.querySelector('.q-type').value;
        const optsContainer = document.getElementById(`options_${qId}`);
        const btnAddOpt = qItem.querySelector('.btn-add-opt');

        if (type === 'text') {
            optsContainer.innerHTML = `<div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-600 text-slate-500 text-xs text-center font-medium">Phần này dành cho người học tự gõ câu trả lời (Dùng làm Khảo sát hoặc Tự luận)</div>`;
            btnAddOpt.style.display = 'none';
        } else {
            optsContainer.innerHTML = '';
            btnAddOpt.style.display = 'inline-flex';
            addOption(qId);
            addOption(qId);
        }
    }

    async function submitCustomForm() {
        const title = document.getElementById('quiz-title').value.trim();
        const desc = document.getElementById('quiz-desc').value.trim();
        
        if (!title) {
            Toastify({ text: "Vui lòng nhập tiêu đề Form", style: { background: "#f59e0b" } }).showToast();
            return;
        }

        const questionItems = document.querySelectorAll('.quiz-question-item');
        if (questionItems.length === 0) {
            Toastify({ text: "Vui lòng thêm ít nhất 1 câu hỏi", style: { background: "#f59e0b" } }).showToast();
            return;
        }

        const schema = {
            settings: { 
                type: 'quiz',
                show_score: true
            },
            questions: []
        };

        let hasError = false;
        let errorMessage = "Vui lòng điền đủ nội dung câu hỏi và đáp án!";

        questionItems.forEach((item) => {
            const qId = item.getAttribute('data-id');
            const qTitle = item.querySelector('.q-title').value.trim();
            const qType = item.querySelector('.q-type').value;

            if (!qTitle) hasError = true;

            const questionData = {
                id: qId,
                type: qType,
                title: qTitle,
                points: qType === 'text' ? 0 : 1,
                options: [],
                correct_answers: []
            };

            if (qType !== 'text') {
                const optItems = item.querySelectorAll('.option-item');
                if (optItems.length < 2) hasError = true;

                let hasCorrectAnswer = false;

                optItems.forEach(optItem => {
                    const inputCheck = optItem.querySelector('input[type="radio"], input[type="checkbox"]');
                    const textInput = optItem.querySelector('.opt-text').value.trim();
                    const optId = inputCheck.value;

                    if (textInput) {
                        questionData.options.push({ id: optId, text: textInput });
                        if (inputCheck.checked) {
                            questionData.correct_answers.push(optId);
                            hasCorrectAnswer = true;
                        }
                    }
                });
                
                if (questionData.options.length < 2) hasError = true;
                
                if (!hasCorrectAnswer) {
                    hasError = true;
                    errorMessage = `Vui lòng tick xanh chọn ít nhất 1 đáp án ĐÚNG cho "${qTitle}"`;
                }
            }

            schema.questions.push(questionData);
        });

        if (hasError) {
            Toastify({ text: errorMessage, style: { background: "#f43f5e" } }).showToast();
            return;
        }

        const payload = {
            title: title,
            description: desc,
            type: 'quiz',
            schema: schema
        };

        try {
            const res = await fetch(`{{ route('app.conversation.quiz.store', $activeConversation->id ?? 0) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeModal('modal-create-quiz');
                document.getElementById('quiz-title').value = '';
                document.getElementById('quiz-desc').value = '';
                document.getElementById('quiz-builder-container').innerHTML = '';
                questionCount = 0;
                Toastify({ text: "Đã xuất bản bài tập thành công!", style: { background: "#10b981" } }).showToast();
            } else {
                Toastify({ text: "Lỗi lưu Form. Hãy thử lại.", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối mạng", style: { background: "#f43f5e" } }).showToast();
        }
    }

    let currentActiveFormId = null;


    async function openQuizLeaderboard(formId) {
        document.getElementById('leaderboard-container').innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div></div>';
        openModal('modal-quiz-leaderboard');

        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/${formId}/results`);
            if (!res.ok) {
                Toastify({ text: "Không thể tải điểm số", style: { background: "#f43f5e" } }).showToast();
                closeModal('modal-quiz-leaderboard');
                return;
            }
            
            const data = await res.json();
            document.getElementById('leaderboard-title').innerText = "Kết quả: " + data.quiz_title;
            
            let html = '';
            
            if (data.submissions.length === 0) {
                html = '<div class="text-center text-slate-500 py-8 text-sm">Chưa có ai nộp bài.</div>';
            } else {
                data.submissions.forEach((sub, index) => {
                    let rankClass = "bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300";
                    if (index === 0) rankClass = "bg-amber-100 dark:bg-amber-900/50 text-amber-500";
                    else if (index === 1) rankClass = "bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200";
                    else if (index === 2) rankClass = "bg-orange-100 dark:bg-orange-900/50 text-orange-500";

                    let detailsHtml = '';
                    if (data.is_owner && sub.answers) {
                        detailsHtml = `
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 space-y-3 hidden" id="details-${sub.id}">
                                <h5 class="text-xs font-bold text-slate-500 uppercase">Chi tiết câu trả lời</h5>
                        `;
                        sub.answers.forEach(ans => {
                            const isCorrect = ans.is_correct;
                            const color = isCorrect ? 'text-emerald-500' : 'text-rose-500';
                            const icon = isCorrect ? 'check-circle' : 'x-circle';
                            detailsHtml += `
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl text-sm">
                                    <p class="font-medium text-slate-700 dark:text-slate-300 mb-1">${ans.question_text}</p>
                                    <div class="flex items-center gap-2 ${color}">
                                        <i data-lucide="${icon}" class="w-4 h-4"></i>
                                        <span class="font-bold text-xs">${ans.answer_text || '(Không trả lời)'}</span>
                                        <span class="ml-auto text-xs text-slate-400">+${ans.points_earned} đ</span>
                                    </div>
                                </div>
                            `;
                        });
                        detailsHtml += `</div>
                            <button type="button" onclick="document.getElementById('details-${sub.id}').classList.toggle('hidden')" class="mt-2 text-[11px] font-bold text-sky-500 hover:text-sky-600 underline">Xem chi tiết</button>
                        `;
                    }

                    html += `
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm ${rankClass}">
                                    #${index + 1}
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/30 text-sky-500 flex items-center justify-center font-bold text-sm shrink-0">
                                    ${sub.user.name.charAt(0).toUpperCase()}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate">${sub.user.name}</h4>
                                    <p class="text-[10px] text-slate-500">${sub.completed_at || 'Không rõ thời gian'}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-emerald-500">${sub.total_score}</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Điểm</div>
                                </div>
                            </div>
                            ${detailsHtml}
                        </div>
                    `;
                });
            }
            
            document.getElementById('leaderboard-container').innerHTML = html;
            lucide.createIcons();

        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            closeModal('modal-quiz-leaderboard');
        }
    }

    async function openQuizRunner(formId) {
        currentActiveFormId = formId;
        document.getElementById('quiz-run-container').innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div></div>';
        document.getElementById('quiz-run-container').style.display = 'block';
        document.getElementById('quiz-result-container').classList.add('hidden');
        document.getElementById('btn-submit-quiz').style.display = 'flex';
        
        openModal('modal-take-quiz');
        
        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/${formId}`);
            if (!res.ok) {
                Toastify({ text: "Không thể tải đề bài", style: { background: "#f43f5e" } }).showToast();
                return;
            }
            const data = await res.json();
            
            document.getElementById('quiz-run-title').innerText = data.title;
            document.getElementById('quiz-run-desc').innerText = data.description || '';
            
            renderQuizForm(data.schema);
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function renderQuizForm(schema) {
        const container = document.getElementById('quiz-run-container');
        container.innerHTML = '';
        
        if (!schema || !schema.questions) return;
        
        schema.questions.forEach((q, idx) => {
            let optionsHtml = '';
            
            if (q.type === 'text') {
                optionsHtml = `<textarea name="ans_${q.id}" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-all resize-none" placeholder="Nhập câu trả lời của bạn..."></textarea>`;
            } else {
                const inputType = q.type === 'radio' ? 'radio' : 'checkbox';
                q.options.forEach(opt => {
                    optionsHtml += `
                        <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 cursor-pointer transition-all group">
                            <input type="${inputType}" name="ans_${q.id}" value="${opt.id}" class="w-4 h-4 text-sky-500 border-slate-300 focus:ring-sky-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-medium select-none">${opt.text}</span>
                        </label>
                    `;
                });
            }
            
            const pointsText = q.points > 0 ? `<span class="ml-2 px-2 py-0.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-bold rounded-md">${q.points} điểm</span>` : '';

            container.innerHTML += `
                <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm mb-4 question-block" data-qid="${q.id}" data-qtype="${q.type}">
                    <h4 class="font-bold text-slate-900 dark:text-white mb-4 flex items-start gap-2">
                        <span class="shrink-0 w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-500 flex items-center justify-center text-xs">${idx + 1}</span>
                        <span>${q.title} ${pointsText}</span>
                    </h4>
                    <div class="space-y-1 ml-8">
                        ${optionsHtml}
                    </div>
                </div>
            `;
        });
    }

    async function submitQuiz() {
        if (!currentActiveFormId) return;
        
        const answers = {};
        const blocks = document.querySelectorAll('.question-block');
        
        blocks.forEach(block => {
            const qId = block.getAttribute('data-qid');
            const qType = block.getAttribute('data-qtype');
            
            if (qType === 'text') {
                answers[qId] = block.querySelector('textarea').value.trim();
            } else {
                const checked = Array.from(block.querySelectorAll('input:checked')).map(el => el.value);
                if (qType === 'radio') {
                    answers[qId] = checked[0] || null;
                } else {
                    answers[qId] = checked;
                }
            }
        });
        
        const btn = document.getElementById('btn-submit-quiz');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang nộp...';
        btn.disabled = true;
        
        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/${currentActiveFormId}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ answers })
            });
            
            const data = await res.json();
            
            if (res.ok) {
                document.getElementById('quiz-run-container').style.display = 'none';
                document.getElementById('btn-submit-quiz').style.display = 'none';
                
                const resultContainer = document.getElementById('quiz-result-container');
                resultContainer.classList.remove('hidden');
                
                document.getElementById('quiz-score').innerText = `${data.score} / ${data.max_score}`;
                Toastify({ text: "Nộp bài thành công!", style: { background: "#10b981" } }).showToast();
            } else {
                if (data.score !== undefined) {
                    document.getElementById('quiz-run-container').style.display = 'none';
                    document.getElementById('btn-submit-quiz').style.display = 'none';
                    const resultContainer = document.getElementById('quiz-result-container');
                    resultContainer.classList.remove('hidden');
                    document.getElementById('quiz-score').innerText = `${data.score} / ${data.max_score}`;
                }
                Toastify({ text: data.message || "Lỗi khi nộp bài", style: { background: "#f59e0b" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            lucide.createIcons();
        }
    }
</script>
