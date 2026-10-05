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
                
                <!-- Action Toolbar: Thả cảm xúc & Reply -->
                @php
                    $replyPreview = $message->type === 'quiz' ? 'Bài kiểm tra: ' . $message->body : ($message->type === 'audio' ? '[Tin nhắn thoại]' : ($message->type === 'image' ? '[Hình ảnh]' : ($message->type === 'game_dice' ? '[Tung xúc xắc]' : ($message->type === 'game_rps' ? '[Oẳn tù tì]' : $message->body))));
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
                </div>

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
        </div>
    </div>
</div>
