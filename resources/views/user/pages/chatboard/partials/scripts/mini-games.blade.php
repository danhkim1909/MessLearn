<script>
function renderRpsCardHtml(message, isMine) {
            const rpsMeta = message.metadata || {};
            const rpsStatus = rpsMeta.status || 'waiting';
            const creatorId = rpsMeta.creator_id || message.user_id;
            const currentUserId = {{ Auth::id() }};
            const isCreator = creatorId === currentUserId;
            const winnerId = rpsMeta.winner_id;

            const choiceLabels = {
                rock: { label: 'Búa', icon: 'shield' },
                paper: { label: 'Bao', icon: 'hand' },
                scissors: { label: 'Kéo', icon: 'scissors' }
            };

            let rpsBody = '';
            if (rpsStatus === 'waiting') {
                if (isCreator) {
                    rpsBody = `
                        <div class="p-2.5 rounded-xl ${isMine ? 'bg-black/15' : 'bg-black/5 dark:bg-white/5'} text-center text-xs opacity-90">
                            <p class="font-medium">Nước đi của bạn được giữ bí mật.</p>
                            <p class="text-[11px] opacity-75 mt-0.5">Đang chờ đối thủ nhận lời thách đấu...</p>
                        </div>
                    `;
                } else {
                    rpsBody = `
                        <div class="p-2.5 rounded-xl ${isMine ? 'bg-black/15' : 'bg-slate-50 dark:bg-slate-900/60'} border ${isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700'} text-center">
                            <p class="text-xs font-bold mb-2">Chọn nước đi để đối đầu:</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="playRpsGame(${message.id}, 'rock')" class="py-2 px-1 rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex flex-col items-center gap-1 shadow-sm transition-all hover:scale-105 active:scale-95" title="Búa">
                                    <i data-lucide="shield" class="w-4 h-4 text-indigo-500"></i>
                                    <span class="text-[10px] font-bold">Búa</span>
                                </button>
                                <button type="button" onclick="playRpsGame(${message.id}, 'paper')" class="py-2 px-1 rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex flex-col items-center gap-1 shadow-sm transition-all hover:scale-105 active:scale-95" title="Bao">
                                    <i data-lucide="hand" class="w-4 h-4 text-indigo-500"></i>
                                    <span class="text-[10px] font-bold">Bao</span>
                                </button>
                                <button type="button" onclick="playRpsGame(${message.id}, 'scissors')" class="py-2 px-1 rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex flex-col items-center gap-1 shadow-sm transition-all hover:scale-105 active:scale-95" title="Kéo">
                                    <i data-lucide="scissors" class="w-4 h-4 text-indigo-500"></i>
                                    <span class="text-[10px] font-bold">Kéo</span>
                                </button>
                            </div>
                        </div>
                    `;
                }
            } else {
                const creatorChoice = rpsMeta.creator_choice || 'rock';
                const opponentChoice = rpsMeta.opponent_choice || 'rock';
                const creatorName = rpsMeta.creator_name || (message.user ? message.user.name : 'Người thách đấu');
                const opponentName = rpsMeta.opponent_name || 'Đối thủ';

                const cItem = choiceLabels[creatorChoice] || choiceLabels.rock;
                const oItem = choiceLabels[opponentChoice] || choiceLabels.rock;

                const isDraw = winnerId === 'draw';
                const isCreatorWinner = winnerId == creatorId;
                const winnerName = isDraw ? 'Hòa' : (isCreatorWinner ? creatorName : opponentName);

                rpsBody = `
                    <div class="p-2.5 rounded-xl ${isMine ? 'bg-black/15' : 'bg-slate-50 dark:bg-slate-900/60'} border ${isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700'} space-y-2">
                        <div class="flex items-center justify-between gap-2 text-center">
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] opacity-75 truncate mb-1">${creatorName}</div>
                                <div class="p-1.5 rounded-lg bg-white dark:bg-slate-800 inline-flex flex-col items-center shadow-xs">
                                    <i data-lucide="${cItem.icon}" class="w-4 h-4 text-indigo-500"></i>
                                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">${cItem.label}</span>
                                </div>
                            </div>
                            <div class="font-black text-xs opacity-60">VS</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] opacity-75 truncate mb-1">${opponentName}</div>
                                <div class="p-1.5 rounded-lg bg-white dark:bg-slate-800 inline-flex flex-col items-center shadow-xs">
                                    <i data-lucide="${oItem.icon}" class="w-4 h-4 text-indigo-500"></i>
                                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">${oItem.label}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-center pt-1 border-t ${isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700'}">
                            ${isDraw ? `
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold text-[10px]">
                                    <i data-lucide="minus-circle" class="w-3 h-3"></i>
                                    HÒA NHAU!
                                </span>
                            ` : `
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-extrabold text-[10px]">
                                    <i data-lucide="trophy" class="w-3 h-3"></i>
                                    ${winnerName} CHIẾN THẮNG!
                                </span>
                            `}
                        </div>
                    </div>
                `;
            }

            return `
                <div id="rps-card-${message.id}" class="flex flex-col gap-2 py-1 min-w-[240px]">
                    <div class="flex items-center justify-between text-xs font-bold border-b ${isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700'} pb-1.5">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="swords" class="w-4 h-4 text-amber-400"></i>
                            <span>Thách đấu Oẳn Tù Tì</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold ${rpsStatus === 'completed' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400'}">
                            ${rpsStatus === 'completed' ? 'Đã xong' : 'Đang chờ'}
                        </span>
                    </div>
                    ${message.body ? `<p class="text-xs italic opacity-90">"${message.body}"</p>` : ''}
                    ${rpsBody}
                </div>
            `;
        }

        function updateReactionsBar(messageId, reactions) {
            const bar = document.getElementById('reactions-bar-' + messageId);
            if (!bar) return;

            if (!reactions || reactions.length === 0) {
                bar.innerHTML = '';
                bar.classList.add('hidden');
                return;
            }

            const currentUserId = {{ Auth::id() }};
            const reactionIconMap = {
                like: { icon: 'thumbs-up', color: 'text-sky-500' },
                heart: { icon: 'heart', color: 'text-rose-500' },
                laugh: { icon: 'smile', color: 'text-amber-500' },
                wow: { icon: 'sparkles', color: 'text-violet-500' },
                sad: { icon: 'frown', color: 'text-blue-400' },
                angry: { icon: 'flame', color: 'text-orange-500' }
            };

            const groups = {};
            reactions.forEach(rx => {
                if (!groups[rx.reaction]) {
                    groups[rx.reaction] = { count: 0, hasMe: false };
                }
                groups[rx.reaction].count++;
                if (rx.user_id === currentUserId) {
                    groups[rx.reaction].hasMe = true;
                }
            });

            const isMine = bar.closest('.justify-end') !== null;
            let html = '';

            Object.keys(groups).forEach(type => {
                const item = groups[type];
                const iconInfo = reactionIconMap[type] || { icon: 'smile', color: 'text-slate-500' };
                const activeClass = item.hasMe 
                    ? 'bg-sky-50 dark:bg-sky-950/60 border-sky-400 text-sky-600 dark:text-sky-400'
                    : (isMine ? 'bg-white/20 border-white/30 text-white' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300');

                html += `
                    <button type="button" onclick="toggleMessageReaction(${messageId}, '${type}')" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border transition-all hover:scale-105 active:scale-95 ${activeClass}" title="${type}">
                        <i data-lucide="${iconInfo.icon}" class="w-3 h-3 ${item.hasMe ? iconInfo.color : ''}"></i>
                        <span>${item.count}</span>
                    </button>
                `;
            });

            bar.innerHTML = html;
            bar.classList.remove('hidden');
            lucide.createIcons();

            if (wasNearBottom) {
                smartScrollToBottom(true, true);
            }
        }

function toggleReactionMenu(messageId) {
            const targetMenu = document.getElementById('reaction-menu-' + messageId);
            if (!targetMenu) return;

            document.querySelectorAll('.reaction-popup').forEach(el => {
                if (el !== targetMenu) {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                }
            });

            targetMenu.classList.toggle('hidden');
            targetMenu.classList.toggle('flex');
        }

        async function toggleMessageReaction(messageId, reactionType) {
            const targetMenu = document.getElementById('reaction-menu-' + messageId);
            if (targetMenu) {
                targetMenu.classList.add('hidden');
                targetMenu.classList.remove('flex');
            }

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/reaction/${messageId}`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ reaction: reactionType })
                });

                if (res.ok) {
                    const data = await res.json();
                    updateReactionsBar(messageId, data.reactions);
                } else {
                    Toastify({ text: "Lỗi thả cảm xúc", style: { background: "#f43f5e" } }).showToast();
                }
            } catch (err) {
                Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            }
        }

        function switchMiniGameTab(tab) {
            const diceTabBtn = document.getElementById('tab-btn-dice');
            const rpsTabBtn = document.getElementById('tab-btn-rps');
            const diceContent = document.getElementById('tab-content-dice');
            const rpsContent = document.getElementById('tab-content-rps');

            if (tab === 'dice') {
                diceTabBtn.className = 'pb-2.5 px-3 border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center gap-2 transition-all';
                rpsTabBtn.className = 'pb-2.5 px-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-xs flex items-center gap-2 transition-all';
                diceContent.classList.remove('hidden');
                rpsContent.classList.add('hidden');
            } else {
                rpsTabBtn.className = 'pb-2.5 px-3 border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center gap-2 transition-all';
                diceTabBtn.className = 'pb-2.5 px-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-xs flex items-center gap-2 transition-all';
                rpsContent.classList.remove('hidden');
                diceContent.classList.add('hidden');
            }
            lucide.createIcons();

            if (wasNearBottom) {
                smartScrollToBottom(true, true);
            }
        }

        function selectRpsChoice(choice) {
            document.getElementById('rps-selected-choice').value = choice;
            const choices = ['rock', 'paper', 'scissors'];
            choices.forEach(c => {
                const btn = document.getElementById('rps-choice-' + c);
                if (!btn) return;
                if (c === choice) {
                    btn.className = 'rps-choice-btn p-3.5 rounded-2xl border-2 border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex flex-col items-center justify-center gap-2 transition-all';
                } else {
                    btn.className = 'rps-choice-btn p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex flex-col items-center justify-center gap-2 transition-all';
                }
            });
        }

        async function submitRollDice() {
            const input = document.getElementById('dice-note-input');
            const note = input ? input.value.trim() : '';
            const btn = document.getElementById('btn-submit-dice');
            const originalText = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang tung...';
                lucide.createIcons();
            }

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/game/dice`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ body: note })
                });

                if (res.ok) {
                    const data = await res.json();
                    appendMessageToChat(data);
                    closeModal('modal-mini-games');
                    if (input) input.value = '';
                    Toastify({ text: "Đã tung xúc xắc thành công!", style: { background: "#6366f1" } }).showToast();
                } else {
                    Toastify({ text: "Lỗi tung xúc xắc", style: { background: "#f43f5e" } }).showToast();
                }
            } catch (err) {
                Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    lucide.createIcons();
                }
            }
        }

        async function submitCreateRps() {
            const choice = document.getElementById('rps-selected-choice').value;
            const input = document.getElementById('rps-note-input');
            const note = input ? input.value.trim() : '';
            const btn = document.getElementById('btn-submit-rps');
            const originalText = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang gửi...';
                lucide.createIcons();
            }

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/game/rps/create`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ choice: choice, body: note })
                });

                if (res.ok) {
                    const data = await res.json();
                    appendMessageToChat(data);
                    closeModal('modal-mini-games');
                    if (input) input.value = '';
                    Toastify({ text: "Đã gửi lời thách đấu Oẳn Tù Tì!", style: { background: "#f59e0b" } }).showToast();
                } else {
                    Toastify({ text: "Lỗi tạo thách đấu", style: { background: "#f43f5e" } }).showToast();
                }
            } catch (err) {
                Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    lucide.createIcons();
                }
            }
        }

        async function playRpsGame(messageId, choice) {
            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/game/rps/${messageId}/play`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ choice: choice })
                });

                if (res.ok) {
                    const data = await res.json();
                    updateMessageInChat(data);
                    Toastify({ text: "Đã hoàn thành lượt đối đầu!", style: { background: "#10b981" } }).showToast();
                } else {
                    const data = await res.json().catch(() => ({}));
                    Toastify({ text: data.error || "Lỗi tham gia đối đầu", style: { background: "#f43f5e" } }).showToast();
                }
            } catch (err) {
                Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            }
        }

        function updateMessageInChat(message) {
            const wasNearBottom = isNearBottom(150);
            const msgEl = document.getElementById('msg-' + message.id);
            if (!msgEl) return;

            if (message.type === 'game_rps') {
                const rpsEl = document.getElementById('rps-card-' + message.id);
                if (rpsEl) {
                    const isMine = message.user_id === {{ Auth::id() }};
                    rpsEl.outerHTML = renderRpsCardHtml(message, isMine);
                }
            } else if (message.type === 'event') {
                const eventEl = document.getElementById('event-card-' + message.id);
                if (eventEl && typeof renderEventCardHtml === 'function') {
                    const isMine = message.user_id === {{ Auth::id() }};
                    eventEl.outerHTML = renderEventCardHtml(message, isMine);
                }
            }

            if (message.reactions) {
                updateReactionsBar(message.id, message.reactions);
            }

            lucide.createIcons();

            if (wasNearBottom) {
                smartScrollToBottom(true, true);
            }
        }
</script>
