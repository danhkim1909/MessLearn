@php
    $isMine = $message->user_id === Auth::id();
    $isRecalled = $message->type === 'recalled';
@endphp
<div id="msg-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
    <div class="flex gap-2 max-w-[75%] {{ $isMine ? 'flex-row-reverse' : 'flex-row' }}">
        @if(!$isMine)
            <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 mt-1 overflow-hidden">
                @if($message->user && $message->user->avatar_url)
                    <img src="{{ $message->user->avatar_url }}" alt="{{ $message->user->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                @endif
            </div>
        @endif
        <div>
            @if(!$isMine)
                <div class="flex items-baseline gap-2 mb-1 ml-1">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $message->user->name }}</span>
                    <span class="text-[10px] text-slate-400">{{ $message->created_at->format('H:i') }}</span>
                </div>
            @else
                <div class="flex items-center gap-1.5 mb-1 mr-1 justify-end text-[10px] text-slate-400">
                    <span>{{ $message->created_at->format('H:i') }}</span>
                    @if(!$activeConversation->is_group)
                        @php
                            $otherPart = $activeConversation->participants->where('user_id', '!=', Auth::id())->first();
                            $isSeen = ($otherPart?->last_read_message_id ?? 0) >= $message->id;
                        @endphp
                        <span id="msg-status-{{ $message->id }}" class="flex items-center" title="{{ $isSeen ? 'Đã xem' : 'Đã gửi' }}">
                            <i data-lucide="{{ $isSeen ? 'check-check' : 'check' }}" class="w-3.5 h-3.5 {{ $isSeen ? 'text-sky-500' : 'text-slate-400' }}"></i>
                        </span>
                    @endif
                </div>
            @endif

            @if($isRecalled)
                <div class="border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-slate-400 dark:text-slate-500 italic px-3.5 py-2 rounded-2xl text-xs max-w-md flex items-center gap-1.5 select-none">
                    <i data-lucide="ban" class="w-3.5 h-3.5 shrink-0 opacity-70"></i>
                    <span>Tin nhắn đã được thu hồi</span>
                </div>
            @else
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
                            @elseif($message->replyTo->type === 'event')
                                [Lịch hẹn]: {{ $message->replyTo->body }}
                            @else
                                {{ $message->replyTo->body }}
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Action Toolbar: Thả cảm xúc & Reply -->
                @php
                    $replyPreview = $message->type === 'quiz' ? 'Bài kiểm tra: ' . $message->body : ($message->type === 'audio' ? '[Tin nhắn thoại]' : ($message->type === 'image' ? '[Hình ảnh]' : ($message->type === 'game_dice' ? '[Tung xúc xắc]' : ($message->type === 'game_rps' ? '[Oẳn tù tì]' : ($message->type === 'event' ? '[Lịch hẹn]: ' . $message->body : $message->body)))));
                @endphp
                <div class="absolute {{ $isMine ? 'right-full mr-2' : 'left-full ml-2' }} top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                    <div class="relative reaction-picker-wrap">
                        <button type="button" onclick="toggleReactionMenu({{ $message->id }})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-amber-500 shadow-sm flex items-center justify-center transition-colors" title="Thả cảm xúc">
                            <i data-lucide="smile" class="w-3.5 h-3.5"></i>
                        </button>
                        <div id="reaction-menu-{{ $message->id }}" class="hidden reaction-popup absolute {{ $isMine ? 'right-0' : 'left-0' }} bottom-full mb-1 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full shadow-lg items-center gap-1 z-30">
                            <button type="button" onclick="toggleMessageReaction({{ $message->id }}, 'like')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-sky-500 hover:scale-125 transition-transform" title="Thích">
                                <i data-lucide="thumbs-up" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="toggleMessageReaction({{ $message->id }}, 'heart')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-rose-500 hover:scale-125 transition-transform" title="Yêu thích">
                                <i data-lucide="heart" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="toggleMessageReaction({{ $message->id }}, 'laugh')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-amber-500 hover:scale-125 transition-transform" title="Haha">
                                <i data-lucide="smile" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="toggleMessageReaction({{ $message->id }}, 'wow')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-violet-500 hover:scale-125 transition-transform" title="Wow">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="toggleMessageReaction({{ $message->id }}, 'sad')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-blue-400 hover:scale-125 transition-transform" title="Buồn">
                                <i data-lucide="frown" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="toggleMessageReaction({{ $message->id }}, 'angry')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-orange-500 hover:scale-125 transition-transform" title="Phẫn nộ">
                                <i data-lucide="flame" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" onclick="prepareReply({{ $message->id }}, '{{ addslashes($message->user->name) }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', \Illuminate\Support\Str::limit($replyPreview, 50))) }}')" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm flex items-center justify-center transition-colors" title="Trả lời">
                        <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="openForwardModal({{ $message->id }})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-indigo-500 shadow-sm flex items-center justify-center transition-colors" title="Chuyển tiếp">
                        <i data-lucide="forward" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="togglePinMessage({{ $message->id }})" id="btn-pin-{{ $message->id }}" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-amber-500 shadow-sm flex items-center justify-center transition-colors" title="{{ $message->is_pinned ? 'Bỏ ghim' : 'Ghim tin nhắn' }}">
                        <i data-lucide="pin" class="w-3.5 h-3.5 {{ $message->is_pinned ? 'text-amber-500 fill-amber-500' : '' }}"></i>
                    </button>
                    @if($isMine)
                        <button type="button" onclick="confirmUnsendMessage({{ $message->id }})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-rose-500 shadow-sm flex items-center justify-center transition-colors" title="Gỡ tin nhắn">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif
                </div>

                <!-- Huy hieu Da ghim -->
                <div id="pin-badge-{{ $message->id }}" class="{{ $message->is_pinned ? 'flex' : 'hidden' }} items-center gap-1 text-[10px] {{ $isMine ? 'text-amber-200' : 'text-amber-500 dark:text-amber-400' }} font-bold mb-1.5 pb-1 border-b {{ $isMine ? 'border-white/20' : 'border-slate-200/60 dark:border-slate-700/60' }}">
                    <i data-lucide="pin" class="w-3 h-3 fill-current"></i>
                    <span>Đã ghim</span>
                </div>

                <!-- Huy hieu Da chuyen tiep -->
                @if(!empty($message->metadata['is_forwarded']))
                    <div class="flex items-center gap-1 text-[10px] {{ $isMine ? 'text-sky-100' : 'text-slate-400 dark:text-slate-400' }} font-medium italic mb-1.5 pb-1 border-b {{ $isMine ? 'border-white/20' : 'border-slate-200/60 dark:border-slate-700/60' }}">
                        <i data-lucide="forward" class="w-3 h-3"></i>
                        <span>Đã chuyển tiếp</span>
                    </div>
                @endif

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
                @elseif($message->type === 'game_dice')
                    @php
                        $diceNum = $message->metadata['dice'] ?? 1;
                    @endphp
                    <div class="flex flex-col gap-2 py-1 min-w-[200px]">
                        <div class="flex items-center gap-2 text-xs font-bold opacity-90 border-b {{ $isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700' }} pb-1.5">
                            <i data-lucide="box" class="w-4 h-4 text-indigo-400"></i>
                            <span>Tung xúc xắc</span>
                        </div>
                        @if($message->body)
                            <p class="text-xs italic opacity-90">"{{ $message->body }}"</p>
                        @endif
                        <div class="flex items-center gap-3 py-1">
                            <div class="w-12 h-12 rounded-2xl {{ $isMine ? 'bg-white text-indigo-600' : 'bg-indigo-500 text-white' }} flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                                {{ $diceNum }}
                            </div>
                            <div>
                                <div class="text-[10px] opacity-75">Kết quả ngẫu nhiên:</div>
                                <div class="font-extrabold text-sm">{{ $diceNum }} điểm</div>
                            </div>
                        </div>
                    </div>
                @elseif($message->type === 'game_rps')
                    @php
                        $rpsMeta = $message->metadata ?? [];
                        $rpsStatus = $rpsMeta['status'] ?? 'waiting';
                        $creatorId = $rpsMeta['creator_id'] ?? $message->user_id;
                        $isCreator = $creatorId === Auth::id();
                        $winnerId = $rpsMeta['winner_id'] ?? null;
                    @endphp
                    <div id="rps-card-{{ $message->id }}" class="flex flex-col gap-2 py-1 min-w-[240px]">
                        <div class="flex items-center justify-between text-xs font-bold border-b {{ $isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700' }} pb-1.5">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="swords" class="w-4 h-4 text-amber-400"></i>
                                <span>Thách đấu Oẳn Tù Tì</span>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $rpsStatus === 'completed' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }}">
                                {{ $rpsStatus === 'completed' ? 'Đã xong' : 'Đang chờ' }}
                            </span>
                        </div>
                        @if($message->body)
                            <p class="text-xs italic opacity-90">"{{ $message->body }}"</p>
                        @endif

                        @if($rpsStatus === 'waiting')
                            @if($isCreator)
                                <div class="p-2.5 rounded-xl {{ $isMine ? 'bg-black/15' : 'bg-black/5 dark:bg-white/5' }} text-center text-xs opacity-90">
                                    <p class="font-medium">Nước đi của bạn được giữ bí mật.</p>
                                    <p class="text-[11px] opacity-75 mt-0.5">Đang chờ đối thủ nhận lời thách đấu...</p>
                                </div>
                            @else
                                <div class="p-2.5 rounded-xl {{ $isMine ? 'bg-black/15' : 'bg-slate-50 dark:bg-slate-900/60' }} border {{ $isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700' }} text-center">
                                    <p class="text-xs font-bold mb-2">Chọn nước đi để đối đầu:</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" onclick="playRpsGame({{ $message->id }}, 'rock')" class="py-2 px-1 rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex flex-col items-center gap-1 shadow-sm transition-all hover:scale-105 active:scale-95" title="Búa">
                                            <i data-lucide="shield" class="w-4 h-4 text-indigo-500"></i>
                                            <span class="text-[10px] font-bold">Búa</span>
                                        </button>
                                        <button type="button" onclick="playRpsGame({{ $message->id }}, 'paper')" class="py-2 px-1 rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex flex-col items-center gap-1 shadow-sm transition-all hover:scale-105 active:scale-95" title="Bao">
                                            <i data-lucide="hand" class="w-4 h-4 text-indigo-500"></i>
                                            <span class="text-[10px] font-bold">Bao</span>
                                        </button>
                                        <button type="button" onclick="playRpsGame({{ $message->id }}, 'scissors')" class="py-2 px-1 rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex flex-col items-center gap-1 shadow-sm transition-all hover:scale-105 active:scale-95" title="Kéo">
                                            <i data-lucide="scissors" class="w-4 h-4 text-indigo-500"></i>
                                            <span class="text-[10px] font-bold">Kéo</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @else
                            @php
                                $creatorChoice = $rpsMeta['creator_choice'] ?? 'rock';
                                $opponentChoice = $rpsMeta['opponent_choice'] ?? 'rock';
                                $creatorName = $rpsMeta['creator_name'] ?? $message->user->name;
                                $opponentName = $rpsMeta['opponent_name'] ?? 'Đối thủ';
                                
                                $choiceLabels = [
                                    'rock' => ['label' => 'Búa', 'icon' => 'shield'],
                                    'paper' => ['label' => 'Bao', 'icon' => 'hand'],
                                    'scissors' => ['label' => 'Kéo', 'icon' => 'scissors']
                                ];

                                $creatorItem = $choiceLabels[$creatorChoice] ?? $choiceLabels['rock'];
                                $opponentItem = $choiceLabels[$opponentChoice] ?? $choiceLabels['rock'];
                                
                                $isDraw = $winnerId === 'draw';
                                $isCreatorWinner = $winnerId == $creatorId;
                                $winnerName = $isDraw ? 'Hòa' : ($isCreatorWinner ? $creatorName : $opponentName);
                            @endphp
                            <div class="p-2.5 rounded-xl {{ $isMine ? 'bg-black/15' : 'bg-slate-50 dark:bg-slate-900/60' }} border {{ $isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700' }} space-y-2">
                                <div class="flex items-center justify-between gap-2 text-center">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] opacity-75 truncate mb-1">{{ $creatorName }}</div>
                                        <div class="p-1.5 rounded-lg bg-white dark:bg-slate-800 inline-flex flex-col items-center shadow-xs">
                                            <i data-lucide="{{ $creatorItem['icon'] }}" class="w-4 h-4 text-indigo-500"></i>
                                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $creatorItem['label'] }}</span>
                                        </div>
                                    </div>
                                    <div class="font-black text-xs opacity-60">VS</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] opacity-75 truncate mb-1">{{ $opponentName }}</div>
                                        <div class="p-1.5 rounded-lg bg-white dark:bg-slate-800 inline-flex flex-col items-center shadow-xs">
                                            <i data-lucide="{{ $opponentItem['icon'] }}" class="w-4 h-4 text-indigo-500"></i>
                                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $opponentItem['label'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center pt-1 border-t {{ $isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700' }}">
                                    @if($isDraw)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold text-[10px]">
                                            <i data-lucide="minus-circle" class="w-3 h-3"></i>
                                            HÒA NHAU!
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-extrabold text-[10px]">
                                            <i data-lucide="trophy" class="w-3 h-3"></i>
                                            {{ $winnerName }} CHIẾN THẮNG!
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($message->type === 'event')
                    @php
                        $meta = $message->metadata ?? [];
                        $eventTitle = $meta['title'] ?? $message->body ?? 'Lịch hẹn học tập';
                        $remindAt = isset($meta['remind_at']) ? \Carbon\Carbon::parse($meta['remind_at']) : null;
                        $location = $meta['location'] ?? '';
                        $note = $meta['note'] ?? '';
                        $participants = $meta['participants'] ?? [];
                        $participantCount = count($participants);
                        $hasJoined = isset($participants[Auth::id()]);
                        $isPast = $remindAt ? $remindAt->isPast() : false;
                    @endphp
                    <div id="event-card-{{ $message->id }}" class="flex flex-col gap-2.5 py-1 min-w-[260px] sm:min-w-[300px]">
                        <!-- Header Su kien -->
                        <div class="flex items-center justify-between border-b {{ $isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700' }} pb-2">
                            <div class="flex items-center gap-1.5 font-bold text-xs {{ $isMine ? 'text-white' : 'text-emerald-600 dark:text-emerald-400' }}">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                <span>LỊCH HẸN HỌC TẬP</span>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $isPast ? 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' : 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' }}">
                                {{ $isPast ? 'Đã diễn ra' : 'Sắp tới' }}
                            </span>
                        </div>

                        <!-- Khoi Thoi gian va Tieu de -->
                        <div class="flex items-start gap-3">
                            @if($remindAt)
                                <div class="w-13 text-center shrink-0 rounded-xl overflow-hidden border {{ $isMine ? 'border-white/20 bg-white/10' : 'border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40' }} shadow-xs">
                                    <div class="bg-emerald-500 text-white text-[9px] uppercase font-bold py-0.5">
                                        Thg {{ $remindAt->format('m') }}
                                    </div>
                                    <div class="py-1">
                                        <div class="font-black text-lg leading-none {{ $isMine ? 'text-white' : 'text-slate-800 dark:text-slate-100' }}">
                                            {{ $remindAt->format('d') }}
                                        </div>
                                        <div class="text-[9px] font-semibold opacity-75 mt-0.5">
                                            {{ $remindAt->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h4 class="font-extrabold text-sm leading-tight {{ $isMine ? 'text-white' : 'text-slate-900 dark:text-white' }} mb-1">
                                    {{ $eventTitle }}
                                </h4>
                                @if($location)
                                    <div class="flex items-center gap-1 text-[11px] opacity-90 truncate mb-1">
                                        <i data-lucide="{{ str_starts_with($location, 'http') ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5 shrink-0"></i>
                                        @if(str_starts_with($location, 'http'))
                                            <a href="{{ $location }}" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-100 font-semibold" onclick="event.stopPropagation()">
                                                Tham gia Online (Mở link)
                                            </a>
                                        @else
                                            <span class="truncate">{{ $location }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if($note)
                                    <p class="text-[11px] opacity-80 italic line-clamp-2">"{{ $note }}"</p>
                                @endif
                            </div>
                        </div>

                        <!-- Footer Tham gia -->
                        <div class="flex items-center justify-between pt-2 border-t {{ $isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700' }}">
                            <div class="text-[11px] opacity-90 flex items-center gap-1">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                <span id="event-count-{{ $message->id }}">{{ $participantCount }} người tham gia</span>
                            </div>
                            <button type="button" onclick="toggleJoinEvent({{ $message->id }})" id="btn-join-event-{{ $message->id }}" class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1 shadow-xs {{ $hasJoined ? 'bg-emerald-500 text-white hover:bg-emerald-600' : ($isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-slate-200 dark:bg-slate-700 hover:bg-emerald-500 hover:text-white text-slate-700 dark:text-slate-200') }}">
                                <i data-lucide="{{ $hasJoined ? 'check' : 'user-plus' }}" class="w-3.5 h-3.5"></i>
                                <span>{{ $hasJoined ? 'Đã tham gia' : 'Tham gia' }}</span>
                            </button>
                        </div>
                    </div>
                @elseif($message->type === 'document')
                    @php
                        $docMeta = $message->metadata ?? [];
                        $fileName = $docMeta['file_name'] ?? 'Tài liệu đính kèm';
                        $ext = strtolower($docMeta['file_extension'] ?? pathinfo($fileName, PATHINFO_EXTENSION));
                        $fileSize = $docMeta['file_size_human'] ?? '';
                        $docUrl = $message->file_url ?? asset('storage/' . $message->file_path);
                        $canPreview = in_array($ext, ['pdf', 'md', 'markdown', 'txt', 'csv', 'tsv', 'json', 'sql', 'py', 'cpp', 'c', 'java', 'html', 'css', 'js', 'log']);

                        $iconName = 'file-text';
                        $iconColor = $isMine ? 'text-slate-600' : 'text-sky-500';
                        $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-sky-500/10 dark:bg-sky-500/20';

                        if ($ext === 'pdf') {
                            $iconName = 'file-text';
                            $iconColor = $isMine ? 'text-rose-600' : 'text-rose-500';
                            $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-rose-500/10 dark:bg-rose-500/20';
                        } elseif (in_array($ext, ['doc', 'docx'])) {
                            $iconName = 'file-text';
                            $iconColor = $isMine ? 'text-blue-600' : 'text-blue-600 dark:text-blue-400';
                            $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800/40';
                        } elseif (in_array($ext, ['xls', 'xlsx', 'csv', 'tsv'])) {
                            $iconName = 'table';
                            $iconColor = $isMine ? 'text-emerald-600' : 'text-emerald-500';
                            $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-emerald-500/10 dark:bg-emerald-500/20';
                        } elseif (in_array($ext, ['ppt', 'pptx'])) {
                            $iconName = 'presentation';
                            $iconColor = $isMine ? 'text-amber-600' : 'text-amber-500';
                            $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-amber-500/10 dark:bg-amber-500/20';
                        } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                            $iconName = 'archive';
                            $iconColor = $isMine ? 'text-orange-600' : 'text-orange-500';
                            $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-orange-500/10 dark:bg-orange-500/20';
                        } elseif (in_array($ext, ['md', 'markdown', 'txt', 'json', 'sql', 'py', 'cpp', 'c', 'java', 'html', 'css', 'js', 'log'])) {
                            $iconName = 'file-code';
                            $iconColor = $isMine ? 'text-purple-600' : 'text-purple-500';
                            $iconBg = $isMine ? 'bg-white shadow-xs' : 'bg-purple-500/10 dark:bg-purple-500/20';
                        }
                    @endphp
                    <div class="flex flex-col gap-2 min-w-[240px] sm:min-w-[280px]">
                        <div class="flex items-center gap-3 p-3 rounded-2xl {{ $isMine ? 'bg-black/10 border border-white/20' : 'bg-black/5 dark:bg-white/5 border border-slate-200 dark:border-slate-700/60' }} transition-all">
                            <div class="w-11 h-11 rounded-xl {{ $iconBg }} {{ $iconColor }} flex items-center justify-center shrink-0 shadow-xs">
                                <i data-lucide="{{ $iconName }}" class="w-6 h-6"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-xs {{ $isMine ? 'text-white' : 'text-slate-800 dark:text-slate-100' }} truncate" title="{{ $fileName }}">
                                    {{ $fileName }}
                                </h4>
                                <div class="flex items-center gap-2 text-[10px] {{ $isMine ? 'text-white/70' : 'text-slate-500 dark:text-slate-400' }} mt-0.5">
                                    <span class="font-medium uppercase">{{ $ext }}</span>
                                    @if($fileSize)
                                        <span>•</span>
                                        <span>{{ $fileSize }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Nut Hanh dong: Xem truc tiep va Tai ve -->
                        <div class="flex items-center gap-2 pt-0.5">
                            @if($canPreview)
                                <button type="button" onclick="openDocumentViewer('{{ $docUrl }}', '{{ addslashes($fileName) }}', '{{ $ext }}')" class="flex-1 py-1.5 px-3 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 {{ $isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white shadow-xs' }}">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Xem trực tiếp</span>
                                </button>
                            @endif
                            <a href="{{ $docUrl }}" download="{{ $fileName }}" class="{{ $canPreview ? 'px-3 py-1.5' : 'flex-1 py-1.5 px-3' }} rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 {{ $isMine ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200' }}" title="Tải tài liệu về máy">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>{{ $canPreview ? 'Tải' : 'Tải tài liệu' }}</span>
                            </a>
                        </div>

                        @if($message->body)
                            <p class="text-xs pt-1 opacity-90 {{ $isMine ? 'text-white' : 'text-slate-800 dark:text-slate-200' }}">{!! nl2br(e($message->body)) !!}</p>
                        @endif
                    </div>
                @else
                    {!! nl2br(e($message->body)) !!}
                @endif

                <!-- Khay hiển thị Reactions đã thả -->
                @php
                    $groupedReactions = $message->reactions ? $message->reactions->groupBy('reaction') : collect();
                    $reactionIconMap = [
                        'like' => ['icon' => 'thumbs-up', 'color' => 'text-sky-500'],
                        'heart' => ['icon' => 'heart', 'color' => 'text-rose-500'],
                        'laugh' => ['icon' => 'smile', 'color' => 'text-amber-500'],
                        'wow' => ['icon' => 'sparkles', 'color' => 'text-violet-500'],
                        'sad' => ['icon' => 'frown', 'color' => 'text-blue-400'],
                        'angry' => ['icon' => 'flame', 'color' => 'text-orange-500'],
                    ];
                @endphp
                <div id="reactions-bar-{{ $message->id }}" class="flex flex-wrap gap-1 mt-1.5 {{ $isMine ? 'justify-end' : 'justify-start' }} {{ $groupedReactions->isEmpty() ? 'hidden' : '' }}">
                    @foreach($groupedReactions as $rxType => $rxItems)
                        @php
                            $hasMe = $rxItems->contains('user_id', Auth::id());
                            $iconInfo = $reactionIconMap[$rxType] ?? ['icon' => 'smile', 'color' => 'text-slate-500'];
                        @endphp
                        <button type="button" onclick="toggleMessageReaction({{ $message->id }}, '{{ $rxType }}')" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border transition-all hover:scale-105 active:scale-95 {{ $hasMe ? 'bg-sky-50 dark:bg-sky-950/60 border-sky-400 text-sky-600 dark:text-sky-400' : ($isMine ? 'bg-white/20 border-white/30 text-white' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300') }}" title="{{ $rxType }}">
                            <i data-lucide="{{ $iconInfo['icon'] }}" class="w-3 h-3 {{ $hasMe ? $iconInfo['color'] : '' }}"></i>
                            <span>{{ $rxItems->count() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Danh sach nguoi da xem dung tai tin nhan nay -->
            @php
                $readersAtThisMessage = $activeConversation->is_group 
                    ? $activeConversation->participants->filter(fn($p) => $p->user_id !== Auth::id() && (int)$p->last_read_message_id === $message->id)
                    : collect();
            @endphp
            <div id="readers-stack-{{ $message->id }}" class="flex items-center -space-x-1 mt-1 {{ $isMine ? 'justify-end' : 'justify-start' }} {{ $readersAtThisMessage->isEmpty() ? 'hidden' : '' }}">
                @foreach($readersAtThisMessage as $p)
                    <div id="reader-avatar-{{ $p->user_id }}-{{ $message->id }}" class="w-3.5 h-3.5 rounded-full border border-white dark:border-slate-800 bg-slate-300 dark:bg-slate-600 overflow-hidden text-[8px] flex items-center justify-center font-bold shrink-0 shadow-xs" title="{{ $p->user->name }} đã xem">
                        @if($p->user && $p->user->avatar_url)
                            <img src="{{ $p->user->avatar_url }}" alt="{{ $p->user->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($p->user?->name ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
