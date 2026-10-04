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
                                                <div class="truncate">
                                                    @if($message->replyTo->type === 'quiz')
                                                        Bài kiểm tra: {{ $message->replyTo->body }}
                                                    @elseif($message->replyTo->type === 'audio')
                                                        [Tin nhắn thoại]
                                                    @elseif($message->replyTo->type === 'image')
                                                        [Hình ảnh] {{ $message->replyTo->body ? ': ' . $message->replyTo->body : '' }}
                                                    @else
                                                        {{ $message->replyTo->body }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Nút Reply -->
                                        @php
                                            $replyPreview = $message->type === 'quiz' ? 'Bài kiểm tra: ' . $message->body : ($message->type === 'audio' ? '[Tin nhắn thoại]' : ($message->type === 'image' ? '[Hình ảnh]' : $message->body));
                                        @endphp
                                        <button onclick="prepareReply({{ $message->id }}, '{{ addslashes($message->user->name) }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', \Illuminate\Support\Str::limit($replyPreview, 50))) }}')" class="absolute {{ $isMine ? 'right-full mr-2' : 'left-full ml-2' }} top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10" title="Trả lời">
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
                                        @elseif($message->type === 'audio')
                                            <div class="flex items-center gap-3 py-1 min-w-[220px]">
                                                <button type="button" onclick="toggleAudioPlay(this)" class="w-8 h-8 rounded-full {{ $isMine ? 'bg-white text-sky-600' : 'bg-sky-500 text-white' }} flex items-center justify-center shrink-0 shadow-sm transition-transform active:scale-95">
                                                    <i data-lucide="play" class="w-4 h-4 ml-0.5 audio-play-icon"></i>
                                                    <i data-lucide="pause" class="w-4 h-4 hidden audio-pause-icon"></i>
                                                </button>
                                                <div class="flex-1 flex flex-col justify-center">
                                                    <div class="w-full bg-black/10 dark:bg-white/10 h-1.5 rounded-full overflow-hidden cursor-pointer audio-progress-container" onclick="seekAudio(event, this)">
                                                        <div class="bg-current h-full w-0 rounded-full transition-all duration-100 audio-progress-bar"></div>
                                                    </div>
                                                    <div class="flex justify-between items-center mt-1 text-[10px] opacity-75">
                                                        <span class="audio-current-time">0:00</span>
                                                        <span class="audio-duration">--:--</span>
                                                    </div>
                                                </div>
                                                <audio src="{{ $message->file_url ?? asset('storage/' . $message->file_path) }}" preload="metadata" class="hidden audio-element" ontimeupdate="updateAudioProgress(this)" onloadedmetadata="initAudioDuration(this)" onended="onAudioEnded(this)"></audio>
                                            </div>
                                            @if($message->body)
                                                <p class="mt-1.5 text-xs">{!! nl2br(e($message->body)) !!}</p>
                                            @endif
                                        @elseif($message->type === 'image')
                                            <div class="space-y-1.5">
                                                <div class="relative group/img overflow-hidden rounded-xl border border-black/5 dark:border-white/5 bg-black/5 max-w-xs sm:max-w-sm">
                                                    <img src="{{ $message->file_url ?? asset('storage/' . $message->file_path) }}" alt="Hình ảnh" class="max-h-72 w-auto max-w-full rounded-xl object-cover cursor-pointer hover:opacity-95 transition-opacity" onclick="openLightbox('{{ $message->file_url ?? asset('storage/' . $message->file_path) }}')">
                                                    <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover/img:opacity-100 transition-opacity bg-slate-900/75 backdrop-blur-xs p-1 rounded-lg">
                                                        <button type="button" onclick="openImageAnnotator('{{ $message->file_url ?? asset('storage/' . $message->file_path) }}', {{ $message->id }})" class="p-1.5 text-white hover:text-sky-400 transition-colors rounded" title="Vẽ chú thích lên ảnh">
                                                            <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <button type="button" onclick="openLightbox('{{ $message->file_url ?? asset('storage/' . $message->file_path) }}')" class="p-1.5 text-white hover:text-sky-400 transition-colors rounded" title="Xem ảnh lớn">
                                                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @if($message->body)
                                                    <p class="text-xs pt-1">{!! nl2br(e($message->body)) !!}</p>
                                                @endif
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

                <form id="chat-form" class="flex items-end gap-2" onsubmit="sendChatMessage(event)">
                    <input type="hidden" id="reply-to-id" value="">
                    <input type="file" id="image-file-input" accept="image/*" class="hidden" onchange="handleImageSelected(event)">
                    <button type="button" onclick="document.getElementById('image-file-input').click()" class="p-3 text-slate-400 hover:text-sky-500 transition-colors" title="Đính kèm ảnh">
                        <i data-lucide="paperclip" class="w-5 h-5"></i>
                    </button>
                    <div class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-1 relative">
                        <textarea id="chat-input" rows="1" class="w-full bg-transparent px-3 py-2 text-sm focus:outline-none dark:text-white resize-none max-h-32" placeholder="Nhập tin nhắn..." onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendChatMessage(event); }"></textarea>
                    </div>
                    <button type="button" id="btn-record-voice" onclick="startVoiceRecording()" class="p-3 text-slate-400 hover:text-sky-500 transition-colors rounded-xl flex items-center justify-center shrink-0" title="Ghi âm">
                        <i data-lucide="mic" class="w-5 h-5"></i>
                    </button>
                    <button type="submit" class="p-3 bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow-md shadow-sky-500/20 transition-all flex items-center justify-center shrink-0">
                        <i data-lucide="send" class="w-5 h-5 ml-1"></i>
                    </button>
                </form>

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

<!-- MODAL: LIGHTBOX XEM ANH LON -->
<div id="modal-lightbox" class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 hidden items-center justify-center p-4" onclick="closeLightbox()">
    <button type="button" onclick="closeLightbox()" class="absolute top-4 right-4 p-2 text-white/70 hover:text-white rounded-full bg-white/10 hover:bg-white/20 transition-colors z-10" title="Đóng">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
    <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[85vh] rounded-2xl object-contain shadow-2xl transition-transform" onclick="event.stopPropagation()">
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
<script src="https://unpkg.com/painterro@1.2.55/build/painterro.min.js"></script>
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
        function formatAudioTime(seconds) {
            if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${s < 10 ? '0' : ''}${s}`;
        }

        function toggleAudioPlay(btn) {
            const container = btn.closest('.min-w-\\[220px\\]');
            if (!container) return;
            const audio = container.querySelector('.audio-element');
            const playIcon = btn.querySelector('.audio-play-icon');
            const pauseIcon = btn.querySelector('.audio-pause-icon');

            if (!audio) return;

            if (audio.paused) {
                document.querySelectorAll('.audio-element').forEach(otherAudio => {
                    if (otherAudio !== audio && !otherAudio.paused) {
                        otherAudio.pause();
                        const otherContainer = otherAudio.closest('.min-w-\\[220px\\]');
                        if (otherContainer) {
                            const otherPlay = otherContainer.querySelector('.audio-play-icon');
                            const otherPause = otherContainer.querySelector('.audio-pause-icon');
                            if (otherPlay) otherPlay.classList.remove('hidden');
                            if (otherPause) otherPause.classList.add('hidden');
                        }
                    }
                });

                audio.play().then(() => {
                    if (playIcon) playIcon.classList.add('hidden');
                    if (pauseIcon) pauseIcon.classList.remove('hidden');
                }).catch(() => {});
            } else {
                audio.pause();
                if (playIcon) playIcon.classList.remove('hidden');
                if (pauseIcon) pauseIcon.classList.add('hidden');
            }
        }

        function updateAudioProgress(audio) {
            const container = audio.closest('.min-w-\\[220px\\]');
            if (!container) return;
            const progressBar = container.querySelector('.audio-progress-bar');
            const currentTimeEl = container.querySelector('.audio-current-time');
            const durationEl = container.querySelector('.audio-duration');

            if (progressBar && audio.duration) {
                const percent = (audio.currentTime / audio.duration) * 100;
                progressBar.style.width = percent + '%';
            }

            if (currentTimeEl) {
                currentTimeEl.innerText = formatAudioTime(audio.currentTime);
            }

            if (durationEl && (!durationEl.dataset.initialized || durationEl.innerText === '--:--')) {
                if (audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
                    durationEl.innerText = formatAudioTime(audio.duration);
                    durationEl.dataset.initialized = 'true';
                }
            }
        }

        function initAudioDuration(audio) {
            const container = audio.closest('.min-w-\\[220px\\]');
            if (!container) return;
            const durationEl = container.querySelector('.audio-duration');
            if (durationEl && audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
                durationEl.innerText = formatAudioTime(audio.duration);
                durationEl.dataset.initialized = 'true';
            }
        }

        function onAudioEnded(audio) {
            const container = audio.closest('.min-w-\\[220px\\]');
            if (!container) return;
            const playIcon = container.querySelector('.audio-play-icon');
            const pauseIcon = container.querySelector('.audio-pause-icon');
            const progressBar = container.querySelector('.audio-progress-bar');
            const currentTimeEl = container.querySelector('.audio-current-time');

            if (playIcon) playIcon.classList.remove('hidden');
            if (pauseIcon) pauseIcon.classList.add('hidden');
            if (progressBar) progressBar.style.width = '0%';
            if (currentTimeEl) currentTimeEl.innerText = '0:00';
            audio.currentTime = 0;
        }

        function seekAudio(event, barContainer) {
            const container = barContainer.closest('.min-w-\\[220px\\]');
            if (!container) return;
            const audio = container.querySelector('.audio-element');
            if (!audio || !audio.duration) return;

            const rect = barContainer.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const percent = Math.max(0, Math.min(1, clickX / rect.width));
            audio.currentTime = percent * audio.duration;
        }

        let mediaRecorder = null;
        let audioChunks = [];
        let recordingStream = null;
        let recordTimerInterval = null;
        let recordSeconds = 0;
        let recordedAudioBlob = null;
        let previewObjectUrl = null;

        function stopRecordingStream() {
            if (recordingStream) {
                recordingStream.getTracks().forEach(track => track.stop());
                recordingStream = null;
            }
        }

        async function startVoiceRecording() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                Toastify({text: "Trình duyệt không hỗ trợ ghi âm", style: {background: "#f43f5e"}}).showToast();
                return;
            }

            try {
                audioChunks = [];
                recordedAudioBlob = null;
                recordSeconds = 0;

                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                recordingStream = stream;

                let options = {};
                if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                    options.mimeType = 'audio/webm;codecs=opus';
                } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                    options.mimeType = 'audio/webm';
                } else if (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')) {
                    options.mimeType = 'audio/ogg;codecs=opus';
                } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                    options.mimeType = 'audio/mp4';
                }

                mediaRecorder = new MediaRecorder(stream, options);

                mediaRecorder.ondataavailable = (e) => {
                    if (e.data && e.data.size > 0) {
                        audioChunks.push(e.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    const mime = mediaRecorder.mimeType || 'audio/webm';
                    recordedAudioBlob = new Blob(audioChunks, { type: mime });
                };

                mediaRecorder.start(200);

                const chatForm = document.getElementById('chat-form');
                const voiceContainer = document.getElementById('voice-recording-container');
                const activeView = document.getElementById('recording-active-view');
                const previewView = document.getElementById('recording-preview-view');
                const timerEl = document.getElementById('recording-timer');

                if (chatForm) chatForm.classList.add('hidden');
                if (voiceContainer) {
                    voiceContainer.classList.remove('hidden');
                    voiceContainer.classList.add('flex');
                }
                if (activeView) activeView.classList.remove('hidden');
                if (previewView) previewView.classList.add('hidden');
                if (timerEl) timerEl.innerText = '00:00';

                clearInterval(recordTimerInterval);
                recordTimerInterval = setInterval(() => {
                    recordSeconds++;
                    const m = Math.floor(recordSeconds / 60);
                    const s = recordSeconds % 60;
                    if (timerEl) {
                        timerEl.innerText = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                    }
                }, 1000);

                lucide.createIcons();
            } catch (err) {
                stopRecordingStream();
                Toastify({text: "Không thể truy cập microphone. Vui lòng cấp quyền!", style: {background: "#f43f5e"}}).showToast();
            }
        }

        function cancelVoiceRecording() {
            clearInterval(recordTimerInterval);
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.onstop = null;
                mediaRecorder.stop();
            }
            stopRecordingStream();

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }

            const previewAudio = document.getElementById('preview-audio-element');
            if (previewAudio) {
                previewAudio.pause();
                previewAudio.src = '';
            }

            audioChunks = [];
            recordedAudioBlob = null;
            recordSeconds = 0;

            const chatForm = document.getElementById('chat-form');
            const voiceContainer = document.getElementById('voice-recording-container');
            if (voiceContainer) {
                voiceContainer.classList.add('hidden');
                voiceContainer.classList.remove('flex');
            }
            if (chatForm) chatForm.classList.remove('hidden');
        }

        function stopAndPreviewVoiceRecording() {
            clearInterval(recordTimerInterval);

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.onstop = () => {
                    const mime = mediaRecorder.mimeType || 'audio/webm';
                    recordedAudioBlob = new Blob(audioChunks, { type: mime });
                    setupPreviewAudio();
                };
                mediaRecorder.stop();
            } else if (recordedAudioBlob) {
                setupPreviewAudio();
            }

            stopRecordingStream();
        }

        function setupPreviewAudio() {
            if (!recordedAudioBlob) return;
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }
            previewObjectUrl = URL.createObjectURL(recordedAudioBlob);

            const previewAudio = document.getElementById('preview-audio-element');
            const activeView = document.getElementById('recording-active-view');
            const previewView = document.getElementById('recording-preview-view');
            const previewTimer = document.getElementById('preview-timer');

            if (previewAudio) {
                previewAudio.src = previewObjectUrl;
            }
            if (activeView) activeView.classList.add('hidden');
            if (previewView) previewView.classList.remove('hidden');
            if (previewTimer) previewTimer.innerText = '00:00';

            lucide.createIcons();
        }

        function togglePreviewAudio() {
            const previewAudio = document.getElementById('preview-audio-element');
            const playIcon = document.getElementById('preview-play-icon');
            const pauseIcon = document.getElementById('preview-pause-icon');

            if (!previewAudio) return;

            if (previewAudio.paused) {
                previewAudio.play().then(() => {
                    if (playIcon) playIcon.classList.add('hidden');
                    if (pauseIcon) pauseIcon.classList.remove('hidden');
                }).catch(() => {});
            } else {
                previewAudio.pause();
                if (playIcon) playIcon.classList.remove('hidden');
                if (pauseIcon) pauseIcon.classList.add('hidden');
            }
        }

        function updatePreviewTimer() {
            const previewAudio = document.getElementById('preview-audio-element');
            const timerEl = document.getElementById('preview-timer');
            if (previewAudio && timerEl) {
                timerEl.innerText = formatAudioTime(previewAudio.currentTime);
            }
        }

        function onPreviewAudioEnded() {
            const playIcon = document.getElementById('preview-play-icon');
            const pauseIcon = document.getElementById('preview-pause-icon');
            const timerEl = document.getElementById('preview-timer');
            if (playIcon) playIcon.classList.remove('hidden');
            if (pauseIcon) pauseIcon.classList.add('hidden');
            if (timerEl) timerEl.innerText = '00:00';
        }

        async function sendVoiceMessage() {
            clearInterval(recordTimerInterval);

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.onstop = async () => {
                    const mime = mediaRecorder.mimeType || 'audio/webm';
                    recordedAudioBlob = new Blob(audioChunks, { type: mime });
                    stopRecordingStream();
                    await submitVoicePayload();
                };
                mediaRecorder.stop();
            } else {
                stopRecordingStream();
                await submitVoicePayload();
            }
        }

        async function submitVoicePayload() {
            if (!recordedAudioBlob) {
                cancelVoiceRecording();
                return;
            }

            const replyInput = document.getElementById('reply-to-id');
            const replyToId = replyInput ? replyInput.value : '';

            const formData = new FormData();
            const extension = recordedAudioBlob.type.includes('ogg') ? 'ogg' : (recordedAudioBlob.type.includes('mp4') ? 'mp4' : 'webm');
            formData.append('audio', recordedAudioBlob, `voice_note.${extension}`);

            if (replyToId) {
                formData.append('reply_to_id', replyToId);
            }

            try {
                const headers = {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                cancelReply();
                cancelVoiceRecording();

                const res = await fetch('{{ route('app.conversation.message.store', $activeConversation->id) }}', {
                    method: 'POST',
                    headers: headers,
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    appendMessageToChat(data);
                } else {
                    Toastify({text: "Lỗi gửi tin nhắn ghi âm", style: {background: "#f43f5e"}}).showToast();
                }
            } catch (err) {
                Toastify({text: "Lỗi kết nối máy chủ", style: {background: "#f43f5e"}}).showToast();
            }
        }

        function appendMessageToChat(message) {
            const chatContainer = document.getElementById('chat-messages-container');
            if(!chatContainer) return;
            const isMine = message.user_id === {{ Auth::id() }};
            const avatarChar = message.user.name.charAt(0).toUpperCase();

            let innerContent = '';
            if (message.reply_to) {
                let replyText = message.reply_to.body;
                if (message.reply_to.type === 'quiz') {
                    replyText = 'Bài kiểm tra: ' + message.reply_to.body;
                } else if (message.reply_to.type === 'audio') {
                    replyText = '[Tin nhắn thoại]';
                } else if (message.reply_to.type === 'image') {
                    replyText = '[Hình ảnh]' + (message.reply_to.body ? ': ' + message.reply_to.body : '');
                }
                innerContent += `
                    <div onclick="scrollToMessage(${message.reply_to_id})" class="cursor-pointer hover:opacity-100 transition-all mb-2 p-2 rounded-xl ${isMine ? 'bg-black/10' : 'bg-black/5 dark:bg-white/5'} border-l-2 ${isMine ? 'border-white/50' : 'border-sky-500'} text-[11px] opacity-80">
                        <div class="font-bold mb-0.5">${message.reply_to.user ? message.reply_to.user.name : ''}</div>
                        <div class="truncate">${replyText || ''}</div>
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
            } else if (message.type === 'audio') {
                const audioSrc = message.file_url || (message.file_path ? `/storage/${message.file_path}` : '');
                innerContent += `
                    <div class="flex items-center gap-3 py-1 min-w-[220px]">
                        <button type="button" onclick="toggleAudioPlay(this)" class="w-8 h-8 rounded-full ${isMine ? 'bg-white text-sky-600' : 'bg-sky-500 text-white'} flex items-center justify-center shrink-0 shadow-sm transition-transform active:scale-95">
                            <i data-lucide="play" class="w-4 h-4 ml-0.5 audio-play-icon"></i>
                            <i data-lucide="pause" class="w-4 h-4 hidden audio-pause-icon"></i>
                        </button>
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="w-full bg-black/10 dark:bg-white/10 h-1.5 rounded-full overflow-hidden cursor-pointer audio-progress-container" onclick="seekAudio(event, this)">
                                <div class="bg-current h-full w-0 rounded-full transition-all duration-100 audio-progress-bar"></div>
                            </div>
                            <div class="flex justify-between items-center mt-1 text-[10px] opacity-75">
                                <span class="audio-current-time">0:00</span>
                                <span class="audio-duration">--:--</span>
                            </div>
                        </div>
                        <audio src="${audioSrc}" preload="metadata" class="hidden audio-element" ontimeupdate="updateAudioProgress(this)" onloadedmetadata="initAudioDuration(this)" onended="onAudioEnded(this)"></audio>
                    </div>
                `;
                if (message.body) {
                    innerContent += `<p class="mt-1.5 text-xs">${(message.body || '').replace(/\n/g, "<br>")}</p>`;
                }
            } else if (message.type === 'image') {
                const imgSrc = message.file_url || (message.file_path ? `/storage/${message.file_path}` : '');
                innerContent += `
                    <div class="space-y-1.5">
                        <div class="relative group/img overflow-hidden rounded-xl border border-black/5 dark:border-white/5 bg-black/5 max-w-xs sm:max-w-sm">
                            <img src="${imgSrc}" alt="Hình ảnh" class="max-h-72 w-auto max-w-full rounded-xl object-cover cursor-pointer hover:opacity-95 transition-opacity" onclick="openLightbox('${imgSrc}')">
                            <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover/img:opacity-100 transition-opacity bg-slate-900/75 backdrop-blur-xs p-1 rounded-lg">
                                <button type="button" onclick="openImageAnnotator('${imgSrc}', ${message.id})" class="p-1.5 text-white hover:text-sky-400 transition-colors rounded" title="Vẽ chú thích lên ảnh">
                                    <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="openLightbox('${imgSrc}')" class="p-1.5 text-white hover:text-sky-400 transition-colors rounded" title="Xem ảnh lớn">
                                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        ${message.body ? `<p class="text-xs pt-1">${(message.body || '').replace(/\n/g, "<br>")}</p>` : ''}
                    </div>
                `;
            } else {
                innerContent += (message.body || '').replace(/\n/g, "<br>");
            }

            let replyTooltip = message.body || '';
            if (message.type === 'quiz') {
                replyTooltip = 'Bài kiểm tra: ' + (message.body || '');
            } else if (message.type === 'audio') {
                replyTooltip = '[Tin nhắn thoại]';
            } else if (message.type === 'image') {
                replyTooltip = '[Hình ảnh]' + (message.body ? ': ' + message.body : '');
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
                                <button onclick="prepareReply(${message.id}, '${(message.user.name || '').replace(/'/g, '\\\'')}', '${replyTooltip.replace(/'/g, '\\\'').replace(/\r\n|\n|\r/g, ' ').substring(0, 50)}')" class="absolute ${isMine ? 'right-full mr-2' : 'left-full ml-2'} top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10" title="Trả lời">
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

        let selectedImageFile = null;
        let selectedImageUrl = null;
        let painterroInstance = null;
        let currentAnnotateReplyId = null;

        function handleImageSelected(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                Toastify({ text: "Vui lòng chọn tệp hình ảnh hợp lệ", style: { background: "#f43f5e" } }).showToast();
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                Toastify({ text: "Kích thước ảnh tối đa 10MB", style: { background: "#f43f5e" } }).showToast();
                return;
            }

            selectedImageFile = file;
            if (selectedImageUrl) {
                URL.revokeObjectURL(selectedImageUrl);
            }
            selectedImageUrl = URL.createObjectURL(file);

            document.getElementById('image-preview-thumbnail').src = selectedImageUrl;
            document.getElementById('image-preview-filename').innerText = file.name;
            const container = document.getElementById('image-preview-container');
            container.classList.remove('hidden');
            container.classList.add('flex');

            document.getElementById('chat-input').focus();
            lucide.createIcons();
        }

        function cancelImageSelection() {
            selectedImageFile = null;
            if (selectedImageUrl) {
                URL.revokeObjectURL(selectedImageUrl);
                selectedImageUrl = null;
            }
            const input = document.getElementById('image-file-input');
            if (input) input.value = '';

            const container = document.getElementById('image-preview-container');
            if (container) {
                container.classList.add('hidden');
                container.classList.remove('flex');
            }
        }

        function annotateSelectedImage() {
            if (!selectedImageUrl) return;
            const replyInput = document.getElementById('reply-to-id');
            const replyToId = replyInput ? replyInput.value : null;
            openImageAnnotator(selectedImageUrl, replyToId);
        }

        function openImageAnnotator(imageUrl, replyToId = null) {
            currentAnnotateReplyId = replyToId;

            if (!painterroInstance) {
                painterroInstance = Painterro({
                    activeColor: '#ef4444',
                    activeColorAlpha: 1,
                    defaultTool: 'brush',
                    saveByEnter: false,
                    colorScheme: {
                        main: '#0ea5e9',
                        control: '#ffffff'
                    },
                    saveHandler: async function (image, done) {
                        try {
                            const blob = image.asBlob('image/png');
                            await submitImagePayload(blob, currentAnnotateReplyId);
                            done(true);
                        } catch (err) {
                            Toastify({ text: "Lỗi lưu ảnh", style: { background: "#f43f5e" } }).showToast();
                            done(false);
                        }
                    }
                });
            }

            painterroInstance.show(imageUrl);
        }

        async function submitImagePayload(blobOrFile, replyToId = null, caption = '') {
            const formData = new FormData();
            formData.append('image', blobOrFile, 'annotated_image.png');

            if (replyToId) {
                formData.append('reply_to_id', replyToId);
            }
            if (caption) {
                formData.append('body', caption);
            }

            const headers = {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            cancelImageSelection();
            cancelReply();

            const res = await fetch('{{ route('app.conversation.message.store', $activeConversation->id ?? 0) }}', {
                method: 'POST',
                headers: headers,
                body: formData
            });

            if (res.ok) {
                const data = await res.json();
                appendMessageToChat(data);
            } else {
                Toastify({ text: "Lỗi gửi ảnh", style: { background: "#f43f5e" } }).showToast();
            }
        }

        function openLightbox(url) {
            const modal = document.getElementById('modal-lightbox');
            const img = document.getElementById('lightbox-img');
            if (modal && img) {
                img.src = url;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeLightbox() {
            const modal = document.getElementById('modal-lightbox');
            const img = document.getElementById('lightbox-img');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (img) img.src = '';
            }
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
            
            if (selectedImageFile) {
                const fileToSend = selectedImageFile;
                const caption = text;
                input.value = '';
                input.style.height = 'auto';
                cancelReply();
                cancelImageSelection();
                input.focus();
                await submitImagePayload(fileToSend, replyToId, caption);
                return;
            }

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
