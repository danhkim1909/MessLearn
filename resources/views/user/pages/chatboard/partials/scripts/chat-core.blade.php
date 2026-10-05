<script>
// Generic Modals & Friend / Group
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    if (id === 'modal-create-quiz') {
        const container = document.getElementById('quiz-builder-container');
        if (container && container.children.length === 0) {
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
        msgEl.className = 'text-xs mt-2 text-rose-500 block';
        return;
    }

    try {
        const res = await fetch('{{ route('app.conversation.store-group') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name, members: userIds })
        });

        const data = await res.json();

        if (res.ok) {
            msgEl.innerText = 'Tạo nhóm thành công!';
            msgEl.className = 'text-xs mt-2 text-emerald-500 block';
            Toastify({text: "Đã tạo nhóm thành công!", style: {background: "#10b981"}}).showToast();
            setTimeout(() => {
                closeModal('modal-create-group');
                window.location.href = `/app/c/${data.id}`;
            }, 1000);
        } else {
            msgEl.innerText = data.message || 'Lỗi tạo nhóm';
            msgEl.className = 'text-xs mt-2 text-rose-500 block';
        }
    } catch (err) {
        msgEl.innerText = 'Lỗi kết nối mạng';
        msgEl.className = 'text-xs mt-2 text-rose-500 block';
    }
}

@if(isset($activeConversation))
    let isLoadingOlderMessages = false;
    let hasMoreOlderMessages = true;
    let oldestMessageId = 0;

    function formatAudioTime(seconds) {
        if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    }

    function buildMessageHtml(message) {
        const isMine = message.user_id === {{ Auth::id() }};
        const avatarChar = (message.user && message.user.name) ? message.user.name.charAt(0).toUpperCase() : 'U';

        let timeStr = 'Vừa xong';
        if (message.created_at) {
            try {
                const dateObj = new Date(message.created_at);
                const hours = String(dateObj.getHours()).padStart(2, '0');
                const mins = String(dateObj.getMinutes()).padStart(2, '0');
                timeStr = `${hours}:${mins}`;
            } catch (e) {
                timeStr = 'Vừa xong';
            }
        }

        let innerContent = '';
        if (message.reply_to) {
            let replyText = message.reply_to.body;
            if (message.reply_to.type === 'quiz') {
                replyText = 'Bài kiểm tra: ' + message.reply_to.body;
            } else if (message.reply_to.type === 'audio') {
                replyText = '[Tin nhắn thoại]';
            } else if (message.reply_to.type === 'image') {
                replyText = '[Hình ảnh]' + (message.reply_to.body ? ': ' + message.reply_to.body : '');
            } else if (message.reply_to.type === 'game_dice') {
                replyText = '[Tung xúc xắc]';
            } else if (message.reply_to.type === 'game_rps') {
                replyText = '[Oẳn tù tì]';
            } else if (message.reply_to.type === 'event') {
                replyText = '[Lịch hẹn]: ' + (message.reply_to.body || '');
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
                        <img src="${imgSrc}" alt="Hình ảnh" class="max-h-72 w-auto max-w-full rounded-xl object-cover cursor-pointer hover:opacity-95 transition-opacity" onclick="openLightbox('${imgSrc}')" onload="if(isNearBottom(250)) smartScrollToBottom(true, true);">
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
        } else if (message.type === 'game_dice') {
            const diceNum = (message.metadata && message.metadata.dice) ? message.metadata.dice : 1;
            innerContent += `
                <div class="flex flex-col gap-2 py-1 min-w-[200px]">
                    <div class="flex items-center gap-2 text-xs font-bold opacity-90 border-b ${isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700'} pb-1.5">
                        <i data-lucide="box" class="w-4 h-4 text-indigo-400"></i>
                        <span>Tung xúc xắc</span>
                    </div>
                    ${message.body ? `<p class="text-xs italic opacity-90">"${message.body}"</p>` : ''}
                    <div class="flex items-center gap-3 py-1">
                        <div class="w-12 h-12 rounded-2xl ${isMine ? 'bg-white text-indigo-600' : 'bg-indigo-500 text-white'} flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                            ${diceNum}
                        </div>
                        <div>
                            <div class="text-[10px] opacity-75">Kết quả ngẫu nhiên:</div>
                            <div class="font-extrabold text-sm">${diceNum} điểm</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (message.type === 'game_rps') {
            innerContent += renderRpsCardHtml(message, isMine);
        } else if (message.type === 'event') {
            innerContent += renderEventCardHtml(message, isMine);
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
        } else if (message.type === 'game_dice') {
            replyTooltip = '[Tung xúc xắc]';
        } else if (message.type === 'game_rps') {
            replyTooltip = '[Oẳn tù tì]';
        } else if (message.type === 'event') {
            replyTooltip = '[Lịch hẹn]: ' + (message.body || '');
        }

        return `
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
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${message.user ? message.user.name : ''}</span>
                                <span class="text-[10px] text-slate-400">${timeStr}</span>
                            </div>
                        ` : `
                            <div class="flex items-baseline gap-2 mb-1 mr-1 justify-end">
                                <span class="text-[10px] text-slate-400">${timeStr}</span>
                            </div>
                        `}
                        <div class="${isMine ? 'bg-sky-500 text-white rounded-tr-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-sm'} px-4 py-2.5 rounded-2xl text-xs max-w-md relative group">
                            <div class="absolute ${isMine ? 'right-full mr-2' : 'left-full ml-2'} top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                                <div class="relative reaction-picker-wrap">
                                    <button type="button" onclick="toggleReactionMenu(${message.id})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-amber-500 shadow-sm flex items-center justify-center transition-colors" title="Thả cảm xúc">
                                        <i data-lucide="smile" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <div id="reaction-menu-${message.id}" class="hidden reaction-popup absolute ${isMine ? 'right-0' : 'left-0'} bottom-full mb-1 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full shadow-lg items-center gap-1 z-30">
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'like')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-sky-500 hover:scale-125 transition-transform" title="Thích">
                                            <i data-lucide="thumbs-up" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'heart')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-rose-500 hover:scale-125 transition-transform" title="Yêu thích">
                                            <i data-lucide="heart" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'laugh')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-amber-500 hover:scale-125 transition-transform" title="Haha">
                                            <i data-lucide="smile" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'wow')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-violet-500 hover:scale-125 transition-transform" title="Wow">
                                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'sad')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-blue-400 hover:scale-125 transition-transform" title="Buồn">
                                            <i data-lucide="frown" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'angry')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-orange-500 hover:scale-125 transition-transform" title="Phẫn nộ">
                                            <i data-lucide="flame" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" onclick="prepareReply(${message.id}, '${(message.user ? message.user.name : '').replace(/'/g, '\\\'')}', '${replyTooltip.replace(/'/g, '\\\'').replace(/\r\n|\n|\r/g, ' ').substring(0, 50)}')" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm flex items-center justify-center transition-colors" title="Trả lời">
                                    <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="togglePinMessage(${message.id})" id="btn-pin-${message.id}" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-amber-500 shadow-sm flex items-center justify-center transition-colors" title="${message.is_pinned ? 'Bỏ ghim' : 'Ghim tin nhắn'}">
                                    <i data-lucide="pin" class="w-3.5 h-3.5 ${message.is_pinned ? 'text-amber-500 fill-amber-500' : ''}"></i>
                                </button>
                            </div>
                            <!-- Huy hieu Da ghim -->
                            <div id="pin-badge-${message.id}" class="${message.is_pinned ? 'flex' : 'hidden'} items-center gap-1 text-[10px] ${isMine ? 'text-amber-200' : 'text-amber-500 dark:text-amber-400'} font-bold mb-1.5 pb-1 border-b ${isMine ? 'border-white/20' : 'border-slate-200/60 dark:border-slate-700/60'}">
                                <i data-lucide="pin" class="w-3 h-3 fill-current"></i>
                                <span>Đã ghim</span>
                            </div>
                            ${innerContent}
                            <div id="reactions-bar-${message.id}" class="flex flex-wrap gap-1 mt-1.5 ${isMine ? 'justify-end' : 'justify-start'} hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Append Message to Chat (Real-time & Sent)
    function appendMessageToChat(message) {
        const chatContainer = document.getElementById('chat-messages-container');
        if(!chatContainer) return;

        const wasNearBottom = isNearBottom(150);
        
        if (document.getElementById('msg-' + message.id)) {
            updateMessageInChat(message);
            return;
        }

        const isMine = message.user_id === {{ Auth::id() }};
        const messageHtml = buildMessageHtml(message);

        // An placeholder rong neu co
        const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
        if (emptyPlaceholder) {
            emptyPlaceholder.remove();
        }

        chatContainer.insertAdjacentHTML('beforeend', messageHtml);
        if (message.reactions && message.reactions.length > 0) {
            updateReactionsBar(message.id, message.reactions);
        }
        lucide.createIcons();

        if (isMine || wasNearBottom) {
            smartScrollToBottom(true, true);
        } else {
            showScrollBottomButton(true);
        }
    }

    // Tai them tin nhan cu khi cuon len dinh (Infinite Scroll / Lazy Load)
    async function loadOlderMessages() {
        const container = document.getElementById('chat-messages-container');
        const loadingEl = document.getElementById('loading-old-messages');
        if (!container || !loadingEl || isLoadingOlderMessages || !hasMoreOlderMessages) return;

        // Tim id cua tin nhan dau tien hien co trong danh sach
        const firstMsgEl = container.querySelector('[id^="msg-"]');
        const beforeId = firstMsgEl ? parseInt(firstMsgEl.id.replace('msg-', '')) : oldestMessageId;

        if (!beforeId || beforeId <= 0) {
            hasMoreOlderMessages = false;
            return;
        }

        isLoadingOlderMessages = true;
        loadingEl.classList.remove('hidden');

        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/messages/load-more?before_id=${beforeId}&limit=25`);
            if (res.ok) {
                const data = await res.json();
                const msgs = data.messages || [];

                if (msgs.length === 0) {
                    hasMoreOlderMessages = false;
                } else {
                    const oldScrollHeight = container.scrollHeight;
                    const oldScrollTop = container.scrollTop;

                    let oldHtml = '';
                    msgs.forEach(msg => {
                        // Neu tin nhan chua co trong DOM thi moi gop vao
                        if (!document.getElementById('msg-' + msg.id)) {
                            oldHtml += buildMessageHtml(msg);
                        }
                    });

                    loadingEl.insertAdjacentHTML('afterend', oldHtml);

                    msgs.forEach(msg => {
                        if (msg.reactions && msg.reactions.length > 0) {
                            updateReactionsBar(msg.id, msg.reactions);
                        }
                    });

                    lucide.createIcons();

                    // Bao toan vi tri cuon: Bu tru do chenh lech chieu cao
                    container.scrollTop = oldScrollTop + (container.scrollHeight - oldScrollHeight);

                    hasMoreOlderMessages = !!data.has_more;
                    oldestMessageId = data.oldest_id || (msgs[0] ? msgs[0].id : 0);
                }
            } else {
                hasMoreOlderMessages = false;
            }
        } catch (err) {
            console.error('Loi tai tin nhan cu:', err);
        } finally {
            loadingEl.classList.add('hidden');
            isLoadingOlderMessages = false;
        }
    }

    // Scroll to Message with Auto Lazy-load if missing
    async function scrollToMessage(id) {
        let el = document.getElementById('msg-' + id);
        if (!el && hasMoreOlderMessages) {
            Toastify({
                text: "Đang tải thêm tin nhắn cũ để tìm tin nhắn gốc...",
                duration: 2000,
                style: { background: "#0284c7" }
            }).showToast();

            await loadOlderMessages();
            el = document.getElementById('msg-' + id);
        }

        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const bubble = el.querySelector('.max-w-md') || el.querySelector('.max-w-xs');
            if (bubble) {
                bubble.classList.add('ring-4', 'ring-amber-300', 'dark:ring-amber-500', 'transition-all');
                setTimeout(() => {
                    bubble.classList.remove('ring-4', 'ring-amber-300', 'dark:ring-amber-500');
                }, 1500);
            }
        } else {
            Toastify({
                text: "Tin nhắn gốc chưa được tải hoặc ở quá xa trong lịch sử trò chuyện",
                duration: 3000,
                style: { background: "#f59e0b" }
            }).showToast();
        }
    }

    function prepareReply(id, name, text) {
        document.getElementById('reply-to-id').value = id;
        document.getElementById('reply-to-name').innerText = name;
        document.getElementById('reply-to-text').innerText = text;
        document.getElementById('reply-preview-container').classList.remove('hidden');
        document.getElementById('chat-input').focus();
    }

    function cancelReply() {
        document.getElementById('reply-to-id').value = '';
        document.getElementById('reply-preview-container').classList.add('hidden');
    }

    async function sendChatMessage(e) {
        if (e) e.preventDefault();

        const input = document.getElementById('chat-input');
        const replyInput = document.getElementById('reply-to-id');
        const text = input.value.trim();
        const replyToId = replyInput.value;

        // Neu co anh dang duoc chon
        const fileToSend = selectedImageFile;
        const caption = text;

        if (fileToSend) {
            await submitImagePayload(fileToSend, caption, replyToId);
            input.value = '';
            cancelReply();
            return;
        }

        if (!text) return;

        input.value = '';
        cancelReply();
        hideTypingIndicator();

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

            const res = await fetch('{{ route('app.conversation.message.store', $activeConversation?->id ?? 0) }}', {
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

    function isNearBottom(threshold = 120) {
        const container = document.getElementById('chat-messages-container');
        if (!container) return true;
        return (container.scrollHeight - container.scrollTop - container.clientHeight) <= threshold;
    }

    function smartScrollToBottom(force = false, smooth = true) {
        const container = document.getElementById('chat-messages-container');
        if (!container) return;

        if (force || isNearBottom()) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
            hideScrollBottomButton();
        } else {
            showScrollBottomButton(true);
        }
    }

    function handleChatScroll() {
        const container = document.getElementById('chat-messages-container');
        if (!container) return;

        // 1. Kiem tra de an hien nut cuon xuong day
        if (isNearBottom(150)) {
            hideScrollBottomButton();
        } else {
            showScrollBottomButton(false);
        }

        // 2. Kiem tra khi cuon len dinh de tai tin nhan cu
        if (container.scrollTop <= 80 && !isLoadingOlderMessages && hasMoreOlderMessages) {
            loadOlderMessages();
        }
    }

    function showScrollBottomButton(hasNewMessage = false) {
        const btn = document.getElementById('btn-scroll-bottom');
        const badge = document.getElementById('scroll-bottom-badge');
        if (!btn) return;
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        if (badge && hasNewMessage) {
            badge.classList.remove('hidden');
        }
    }

    function hideScrollBottomButton() {
        const btn = document.getElementById('btn-scroll-bottom');
        const badge = document.getElementById('scroll-bottom-badge');
        if (!btn) return;
        btn.classList.add('hidden');
        btn.classList.remove('flex');
        if (badge) {
            badge.classList.add('hidden');
        }
    }

    // Xu ly Trang thai dang soan tin nhan (Typing Indicator)
    let lastWhisperTime = 0;
    let typingDisplayTimeout = null;

    function handleChatInputTyping() {
        const input = document.getElementById('chat-input');
        if (!input) return;

        if (input.value.trim().length === 0) {
            return;
        }

        if (typeof window.Echo !== 'undefined') {
            const now = Date.now();
            if (now - lastWhisperTime > 1500) {
                lastWhisperTime = now;
                window.Echo.private('conversation.{{ $activeConversation?->id ?? 0 }}')
                    .whisper('typing', {
                        user_id: {{ Auth::id() }},
                        user_name: '{{ addslashes(Auth::user()->name) }}'
                    });
            }
        }
    }

    function showTypingIndicator(userName) {
        const indicator = document.getElementById('typing-indicator');
        const textEl = document.getElementById('typing-indicator-text');
        const headerStatus = document.getElementById('chat-header-status');
        if (!indicator || !textEl) return;

        textEl.innerText = `${userName} đang soạn tin nhắn...`;
        indicator.classList.remove('hidden');
        indicator.classList.add('flex');

        if (headerStatus) {
            headerStatus.innerText = 'Đang soạn tin nhắn...';
            headerStatus.className = 'text-xs text-sky-500 font-medium animate-pulse';
        }

        if (isNearBottom(150)) {
            smartScrollToBottom(true, false);
        }

        if (typingDisplayTimeout) {
            clearTimeout(typingDisplayTimeout);
        }

        typingDisplayTimeout = setTimeout(() => {
            hideTypingIndicator();
        }, 3000);
    }

    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        const headerStatus = document.getElementById('chat-header-status');
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('flex');
        }
        if (headerStatus) {
            headerStatus.innerText = 'Đang hoạt động';
            headerStatus.className = 'text-xs text-emerald-500 font-medium';
        }
    }

    // Xu ly Tim kiem tin nhan (Search in Chat)
    let searchDebounceTimer = null;

    function toggleChatSearch() {
        const panel = document.getElementById('chat-search-panel');
        if (!panel) return;
        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            const input = document.getElementById('chat-search-input');
            if (input) {
                input.focus();
            }
        } else {
            closeChatSearch();
        }
    }

    function closeChatSearch() {
        const panel = document.getElementById('chat-search-panel');
        if (panel) {
            panel.classList.add('hidden');
        }
        clearChatSearchInput();
    }

    function clearChatSearchInput() {
        const input = document.getElementById('chat-search-input');
        const clearBtn = document.getElementById('btn-clear-search');
        const resultsContainer = document.getElementById('chat-search-results-container');
        const statusEl = document.getElementById('chat-search-status');

        if (input) input.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
        }
        if (statusEl) {
            statusEl.innerText = '';
            statusEl.classList.add('hidden');
        }
    }

    function escapeHtmlText(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function highlightKeyword(text, keyword) {
        if (!text) return '';
        if (!keyword) return escapeHtmlText(text);

        const escapedText = escapeHtmlText(text);
        const escapedKeyword = escapeHtmlText(keyword);
        const regex = new RegExp(`(${escapedKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escapedText.replace(regex, '<mark class="bg-amber-200 dark:bg-amber-800 text-slate-900 dark:text-white rounded px-0.5">$1</mark>');
    }

    function handleChatSearchInput(keyword) {
        const clearBtn = document.getElementById('btn-clear-search');
        const resultsContainer = document.getElementById('chat-search-results-container');
        const statusEl = document.getElementById('chat-search-status');

        const trimmed = (keyword || '').trim();
        if (clearBtn) {
            if (trimmed.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        if (trimmed.length === 0) {
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
                resultsContainer.classList.add('hidden');
            }
            if (statusEl) {
                statusEl.innerText = '';
                statusEl.classList.add('hidden');
            }
            return;
        }

        if (statusEl) {
            statusEl.innerText = 'Đang tìm kiếm...';
            statusEl.classList.remove('hidden');
        }

        searchDebounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/messages/search?q=${encodeURIComponent(trimmed)}`);
                if (res.ok) {
                    const data = await res.json();
                    const list = data.results || [];
                    renderChatSearchResults(list, trimmed);
                } else {
                    if (statusEl) statusEl.innerText = 'Lỗi máy chủ khi tìm kiếm';
                }
            } catch (err) {
                if (statusEl) statusEl.innerText = 'Lỗi kết nối khi tìm kiếm';
            }
        }, 300);
    }

    function renderChatSearchResults(results, keyword) {
        const resultsContainer = document.getElementById('chat-search-results-container');
        const statusEl = document.getElementById('chat-search-status');
        if (!resultsContainer || !statusEl) return;

        resultsContainer.innerHTML = '';

        if (results.length === 0) {
            resultsContainer.classList.add('hidden');
            statusEl.innerText = 'Không tìm thấy tin nhắn nào phù hợp.';
            statusEl.classList.remove('hidden');
            return;
        }

        statusEl.innerText = `Tìm thấy ${results.length} tin nhắn:`;
        statusEl.classList.remove('hidden');

        results.forEach(item => {
            const row = document.createElement('div');
            row.className = 'p-2 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-xl cursor-pointer transition-colors';
            row.onclick = () => jumpToSearchedMessage(item.id);

            const highlightedSnippet = highlightKeyword(item.body || '', keyword);

            row.innerHTML = `
                <div class="flex items-center justify-between text-[11px] mb-0.5">
                    <span class="font-bold text-slate-700 dark:text-slate-200">${escapeHtmlText(item.user_name)}</span>
                    <span class="text-slate-400 text-[10px]">${escapeHtmlText(item.created_at)}</span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2">
                    ${highlightedSnippet}
                </div>
            `;
            resultsContainer.appendChild(row);
        });

        resultsContainer.classList.remove('hidden');
    }

    async function jumpToSearchedMessage(targetId) {
        closeChatSearch();

        let el = document.getElementById('msg-' + targetId);
        if (!el && hasMoreOlderMessages) {
            Toastify({
                text: "Đang tải tin nhắn cũ để di chuyển tới vị trí...",
                duration: 2500,
                style: { background: "#0284c7" }
            }).showToast();

            const container = document.getElementById('chat-messages-container');
            const loadingEl = document.getElementById('loading-old-messages');
            if (container && loadingEl) {
                const firstMsgEl = container.querySelector('[id^="msg-"]');
                const beforeId = firstMsgEl ? parseInt(firstMsgEl.id.replace('msg-', '')) : oldestMessageId;

                if (beforeId && beforeId > 0) {
                    loadingEl.classList.remove('hidden');
                    try {
                        const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/messages/load-more?before_id=${beforeId}&target_id=${targetId}`);
                        if (res.ok) {
                            const data = await res.json();
                            const msgs = data.messages || [];
                            if (msgs.length > 0) {
                                const oldScrollHeight = container.scrollHeight;
                                const oldScrollTop = container.scrollTop;

                                let oldHtml = '';
                                msgs.forEach(msg => {
                                    if (!document.getElementById('msg-' + msg.id)) {
                                        oldHtml += buildMessageHtml(msg);
                                    }
                                });

                                loadingEl.insertAdjacentHTML('afterend', oldHtml);
                                msgs.forEach(msg => {
                                    if (msg.reactions && msg.reactions.length > 0) {
                                        updateReactionsBar(msg.id, msg.reactions);
                                    }
                                });
                                lucide.createIcons();

                                container.scrollTop = oldScrollTop + (container.scrollHeight - oldScrollHeight);
                                hasMoreOlderMessages = !!data.has_more;
                                oldestMessageId = data.oldest_id || (msgs[0] ? msgs[0].id : 0);
                            }
                        }
                    } catch (err) {
                        console.error('Loi khi tai tin nhan tim kiem:', err);
                    } finally {
                        loadingEl.classList.add('hidden');
                    }
                }
            }
        }

        scrollToMessage(targetId);
    }

    // Xu ly Lich nhac hen hoc tap (Study Event Reminders)
    function renderEventCardHtml(message, isMine) {
        const meta = message.metadata || {};
        const eventTitle = meta.title || message.body || 'Lịch hẹn học tập';
        const remindAtStr = meta.remind_at || '';
        const location = meta.location || '';
        const note = meta.note || '';
        const participants = meta.participants || {};
        const participantCount = Object.keys(participants).length;
        const currentUserId = {{ Auth::id() }};
        const hasJoined = !!participants[currentUserId];

        let dateBoxHtml = '';
        let isPast = false;

        if (remindAtStr) {
            try {
                const dateObj = new Date(remindAtStr);
                isPast = dateObj.getTime() < Date.now();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                const hours = String(dateObj.getHours()).padStart(2, '0');
                const mins = String(dateObj.getMinutes()).padStart(2, '0');

                dateBoxHtml = `
                    <div class="w-13 text-center shrink-0 rounded-xl overflow-hidden border ${isMine ? 'border-white/20 bg-white/10' : 'border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40'} shadow-xs">
                        <div class="bg-emerald-500 text-white text-[9px] uppercase font-bold py-0.5">
                            Thg ${month}
                        </div>
                        <div class="py-1">
                            <div class="font-black text-lg leading-none ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-100'}">
                                ${day}
                            </div>
                            <div class="text-[9px] font-semibold opacity-75 mt-0.5">
                                ${hours}:${mins}
                            </div>
                        </div>
                    </div>
                `;
            } catch(e) {}
        }

        let locationHtml = '';
        if (location) {
            const isLink = location.startsWith('http://') || location.startsWith('https://');
            const iconName = isLink ? 'video' : 'map-pin';
            const displayLoc = isLink 
                ? `<a href="${location}" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-100 font-semibold" onclick="event.stopPropagation()">Tham gia Online (Mở link)</a>`
                : `<span class="truncate">${escapeHtmlText(location)}</span>`;

            locationHtml = `
                <div class="flex items-center gap-1 text-[11px] opacity-90 truncate mb-1">
                    <i data-lucide="${iconName}" class="w-3.5 h-3.5 shrink-0"></i>
                    ${displayLoc}
                </div>
            `;
        }

        const noteHtml = note ? `<p class="text-[11px] opacity-80 italic line-clamp-2">"${escapeHtmlText(note)}"</p>` : '';

        return `
            <div id="event-card-${message.id}" class="flex flex-col gap-2.5 py-1 min-w-[260px] sm:min-w-[300px]">
                <div class="flex items-center justify-between border-b ${isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700'} pb-2">
                    <div class="flex items-center gap-1.5 font-bold text-xs ${isMine ? 'text-white' : 'text-emerald-600 dark:text-emerald-400'}">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>LỊCH HẸN HỌC TẬP</span>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${isPast ? 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' : 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'}">
                        ${isPast ? 'Đã diễn ra' : 'Sắp tới'}
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    ${dateBoxHtml}
                    <div class="flex-1 min-w-0">
                        <h4 class="font-extrabold text-sm leading-tight ${isMine ? 'text-white' : 'text-slate-900 dark:text-white'} mb-1">
                            ${escapeHtmlText(eventTitle)}
                        </h4>
                        ${locationHtml}
                        ${noteHtml}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t ${isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700'}">
                    <div class="text-[11px] opacity-90 flex items-center gap-1">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span id="event-count-${message.id}">${participantCount} người tham gia</span>
                    </div>
                    <button type="button" onclick="toggleJoinEvent(${message.id})" id="btn-join-event-${message.id}" class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1 shadow-xs ${hasJoined ? 'bg-emerald-500 text-white hover:bg-emerald-600' : (isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-slate-200 dark:bg-slate-700 hover:bg-emerald-500 hover:text-white text-slate-700 dark:text-slate-200')}">
                        <i data-lucide="${hasJoined ? 'check' : 'user-plus'}" class="w-3.5 h-3.5"></i>
                        <span>${hasJoined ? 'Đã tham gia' : 'Tham gia'}</span>
                    </button>
                </div>
            </div>
        `;
    }

    async function submitCreateEvent(e) {
        if (e) e.preventDefault();

        const titleInput = document.getElementById('event-title');
        const remindAtInput = document.getElementById('event-remind-at');
        const locationInput = document.getElementById('event-location');
        const noteInput = document.getElementById('event-note');
        const errorEl = document.getElementById('create-event-error');
        const submitBtn = document.getElementById('btn-submit-event');

        if (!titleInput || !remindAtInput) return;

        const title = titleInput.value.trim();
        const remindAt = remindAtInput.value;

        if (!title) {
            if (errorEl) {
                errorEl.innerText = 'Vui lòng nhập tiêu đề buổi hẹn!';
                errorEl.classList.remove('hidden');
            }
            return;
        }

        if (!remindAt) {
            if (errorEl) {
                errorEl.innerText = 'Vui lòng chọn thời gian bắt đầu!';
                errorEl.classList.remove('hidden');
            }
            return;
        }

        if (errorEl) errorEl.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const remindBeforeInput = document.getElementById('event-remind-before');
            const remindBefore = remindBeforeInput ? (parseInt(remindBeforeInput.value) || 0) : 15;

            const payload = {
                title: title,
                remind_at: remindAt,
                remind_before: remindBefore,
                location: locationInput ? locationInput.value.trim() : '',
                note: noteInput ? noteInput.value.trim() : ''
            };

            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const res = await fetch(`{{ route('app.conversation.event.create', $activeConversation?->id ?? 0) }}`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                closeModal('modal-create-event');
                document.getElementById('create-event-form').reset();
                appendMessageToChat(data);
                setUpcomingReminderBanner(data);
                Toastify({
                    text: "Đã tạo lịch nhắc hẹn học tập thành công!",
                    style: { background: "#10b981" }
                }).showToast();
            } else {
                Toastify({ text: "Lỗi tạo lịch hẹn", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối máy chủ", style: { background: "#f43f5e" } }).showToast();
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    async function toggleJoinEvent(messageId) {
        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/event/${messageId}/join`, {
                method: 'POST',
                headers: headers
            });

            if (res.ok) {
                const data = await res.json();
                updateMessageInChat(data);
                const meta = data.metadata || {};
                const participants = meta.participants || {};
                const hasJoined = !!participants[{{ Auth::id() }}];
                Toastify({
                    text: hasJoined ? "Bạn đã xác nhận tham gia buổi học!" : "Bạn đã hủy tham gia buổi học",
                    style: { background: hasJoined ? "#10b981" : "#64748b" }
                }).showToast();
            } else {
                Toastify({ text: "Lỗi tham gia lịch hẹn", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối máy chủ", style: { background: "#f43f5e" } }).showToast();
        }
    }

    // He thong Canh bao & Am thanh thong bao (Web Audio API - Khong can file mp3 ngoai)
    function playReminderChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            // Tieng ting thu 1: Not E5 (659.25Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Tieng ting thu 2: Not Ab5 (830.61Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(830.61, now + 0.15);
            gain2.gain.setValueAtTime(0.3, now + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.15);
            osc2.stop(now + 0.55);
        } catch (e) {
            console.warn('Audio chime warning:', e);
        }
    }

    // Thong bao trinh duyet (HTML5 Notification API)
    function triggerBrowserNotification(title, body) {
        if (!("Notification" in window)) return;
        if (Notification.permission === "granted") {
            try {
                new Notification(title, {
                    body: body,
                    icon: '/favicon.ico'
                });
            } catch (e) {
                console.warn('Notification warning:', e);
            }
        }
    }

    // An thanh banner nhac hen hien tai
    function dismissReminderBanner(e) {
        if (e) e.stopPropagation();
        const banner = document.getElementById('upcoming-reminder-banner');
        if (banner) {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }
    }

    // Cuon man hinh toi tin nhan lich hen
    function jumpToReminderEvent() {
        const banner = document.getElementById('upcoming-reminder-banner');
        if (!banner) return;
        const eventId = parseInt(banner.getAttribute('data-event-id'));
        if (eventId > 0) {
            jumpToSearchedMessage(eventId);
        }
    }

    // Cap nhat Banner khi nhan su kien moi hoac tao moi
    function setUpcomingReminderBanner(message) {
        if (!message || message.type !== 'event') return;
        const banner = document.getElementById('upcoming-reminder-banner');
        if (!banner) return;

        const meta = message.metadata || {};
        const newRemindAtStr = meta.remind_at;
        if (!newRemindAtStr) return;

        const newTarget = new Date(newRemindAtStr.replace(' ', 'T')).getTime();
        if (isNaN(newTarget)) return;

        const now = Date.now();
        // Neu su kien da qua hon 2 tieng thi khong can hien
        if (newTarget < now - 2 * 3600 * 1000) return;

        const currentEventId = parseInt(banner.getAttribute('data-event-id')) || 0;
        const currentRemindAtStr = banner.getAttribute('data-remind-at');
        const isHidden = banner.classList.contains('hidden');

        let shouldUpdate = false;
        if (isHidden || currentEventId === 0 || !currentRemindAtStr) {
            shouldUpdate = true;
        } else {
            const currentTarget = new Date(currentRemindAtStr.replace(' ', 'T')).getTime();
            if (currentEventId === message.id || newTarget <= currentTarget) {
                shouldUpdate = true;
            }
        }

        if (shouldUpdate) {
            banner.setAttribute('data-event-id', message.id);
            banner.setAttribute('data-remind-at', newRemindAtStr);
            banner.setAttribute('data-remind-before', meta.remind_before || 15);
            banner.setAttribute('data-location', meta.location || '');

            const titleEl = document.getElementById('reminder-banner-title');
            if (titleEl) {
                titleEl.innerText = meta.title || message.body || '';
            }

            const timeEl = document.getElementById('reminder-banner-time');
            if (timeEl) {
                timeEl.removeAttribute('data-formatted');
            }

            const joinLinkBtn = document.getElementById('btn-reminder-join-link');
            if (joinLinkBtn) {
                const loc = (meta.location || '').trim();
                const isOnline = loc.startsWith('http://') || loc.startsWith('https://');
                if (isOnline) {
                    joinLinkBtn.href = loc;
                    joinLinkBtn.classList.remove('hidden');
                    joinLinkBtn.classList.add('flex');
                } else {
                    joinLinkBtn.href = '#';
                    joinLinkBtn.classList.add('hidden');
                    joinLinkBtn.classList.remove('flex');
                }
            }

            banner.classList.remove('hidden');
            banner.classList.add('flex');
            lucide.createIcons();

            updateReminderBannerCountdown();
        }
    }

    // Bo dem nguoc thoi gian cho Banner va kich hoat Canh bao
    function updateReminderBannerCountdown() {
        const banner = document.getElementById('upcoming-reminder-banner');
        if (!banner || banner.classList.contains('hidden')) return;

        const eventId = banner.getAttribute('data-event-id');
        const remindAtStr = banner.getAttribute('data-remind-at');
        const remindBefore = parseInt(banner.getAttribute('data-remind-before')) || 15;
        const countdownEl = document.getElementById('reminder-banner-countdown');
        const timeEl = document.getElementById('reminder-banner-time');
        const titleEl = document.getElementById('reminder-banner-title');
        const eventTitle = titleEl ? titleEl.innerText : 'Lịch hẹn học tập';

        if (!remindAtStr || !countdownEl) return;

        const targetTime = new Date(remindAtStr.replace(' ', 'T')).getTime();
        if (isNaN(targetTime)) return;

        // Dinh dang gio:phut ngay/thang
        if (timeEl && !timeEl.getAttribute('data-formatted')) {
            const d = new Date(targetTime);
            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            timeEl.innerText = `${hours}:${mins}, ${day}/${month}`;
            timeEl.setAttribute('data-formatted', '1');
        }

        const now = Date.now();
        const diff = targetTime - now;

        if (diff > 0) {
            // Su kien sap dien ra
            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);

            let countdownText = 'Còn ';
            if (days > 0) {
                countdownText += `${days} ngày ${hours} giờ`;
            } else if (hours > 0) {
                countdownText += `${hours} giờ ${minutes} phút`;
            } else if (minutes > 0) {
                countdownText += `${minutes} phút ${seconds < 10 ? '0' : ''}${seconds}s`;
            } else {
                countdownText += `${seconds} giây`;
            }

            countdownEl.innerText = countdownText;
            countdownEl.className = 'font-bold text-amber-600 dark:text-amber-400';

            // Kiem tra nguong bao chuong truoc
            const thresholdMs = remindBefore * 60 * 1000;
            if (diff <= thresholdMs) {
                const sessionKey = `alerted_event_${eventId}_${remindBefore}`;
                if (!sessionStorage.getItem(sessionKey)) {
                    sessionStorage.setItem(sessionKey, '1');
                    playReminderChime();
                    const remainMinutes = Math.max(1, Math.ceil(diff / 60000));
                    triggerBrowserNotification(
                        'Sắp đến lịch hẹn học tập!',
                        `Buổi học "${eventTitle}" sẽ bắt đầu sau ${remainMinutes} phút!`
                    );
                    Toastify({
                        text: `Sắp đến giờ học: ${eventTitle} (sau ${remainMinutes} phút)`,
                        duration: 6000,
                        style: { background: "#f59e0b" }
                    }).showToast();
                }
            }
        } else if (diff > -7200000) {
            // Dang dien ra (trong vong 2 tieng ke tu gio hen)
            countdownEl.innerText = 'Đang diễn ra';
            countdownEl.className = 'font-bold text-emerald-600 dark:text-emerald-400 animate-pulse';

            // Thong bao dung gio bat dau (neu chua bao)
            const startKey = `started_event_${eventId}`;
            if (!sessionStorage.getItem(startKey)) {
                sessionStorage.setItem(startKey, '1');
                playReminderChime();
                triggerBrowserNotification(
                    'Lịch học đang diễn ra!',
                    `Buổi học "${eventTitle}" đã bắt đầu. Hãy tham gia ngay!`
                );
                Toastify({
                    text: `Buổi học "${eventTitle}" đang diễn ra!`,
                    duration: 6000,
                    style: { background: "#10b981" }
                }).showToast();
            }
        } else {
            // Da ket thuc qua 2 tieng -> An banner
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }
    }

    // Xu ly Ghim / Bo ghim tin nhan (Pin Messages)
    async function togglePinMessage(messageId) {
        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/message/${messageId}/pin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (res.ok) {
                const data = await res.json();
                handleMessagePinnedUpdated(data.message);
                Toastify({
                    text: data.is_pinned ? "Đã ghim tin nhắn" : "Đã bỏ ghim tin nhắn",
                    duration: 2000,
                    style: { background: data.is_pinned ? "#0284c7" : "#64748b" }
                }).showToast();
            } else {
                Toastify({ text: "Lỗi ghim tin nhắn", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function unpinCurrentMessage(e) {
        if (e) e.stopPropagation();
        const bar = document.getElementById('pinned-message-bar');
        if (!bar) return;
        const pinnedId = parseInt(bar.getAttribute('data-pinned-id'));
        if (pinnedId > 0) {
            togglePinMessage(pinnedId);
        }
    }

    function jumpToPinnedMessage() {
        const bar = document.getElementById('pinned-message-bar');
        if (!bar) return;
        const pinnedId = parseInt(bar.getAttribute('data-pinned-id'));
        if (pinnedId > 0) {
            jumpToSearchedMessage(pinnedId);
        }
    }

    function handleMessagePinnedUpdated(message) {
        if (!message) return;

        // 1. Cap nhat huy hieu va nut pin tren the tin nhan (neu da co trong DOM)
        const badge = document.getElementById(`pin-badge-${message.id}`);
        const btn = document.getElementById(`btn-pin-${message.id}`);

        if (badge) {
            if (message.is_pinned) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        if (btn) {
            btn.title = message.is_pinned ? 'Bỏ ghim' : 'Ghim tin nhắn';
            const icon = btn.querySelector('svg') || btn.querySelector('i');
            if (icon) {
                if (message.is_pinned) {
                    icon.classList.add('text-amber-500', 'fill-amber-500');
                } else {
                    icon.classList.remove('text-amber-500', 'fill-amber-500');
                }
            }
        }

        // 2. Cap nhat thanh ghim duoi Header
        const bar = document.getElementById('pinned-message-bar');
        const senderNameEl = document.getElementById('pinned-sender-name');
        const previewEl = document.getElementById('pinned-message-preview');

        if (!bar || !senderNameEl || !previewEl) return;

        if (message.is_pinned) {
            // Bo ghim cac tin khac trong DOM
            document.querySelectorAll('[id^="pin-badge-"]').forEach(el => {
                if (el.id !== `pin-badge-${message.id}`) {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                }
            });
            document.querySelectorAll('[id^="btn-pin-"]').forEach(el => {
                if (el.id !== `btn-pin-${message.id}`) {
                    el.title = 'Ghim tin nhắn';
                    const icon = el.querySelector('svg') || el.querySelector('i');
                    if (icon) icon.classList.remove('text-amber-500', 'fill-amber-500');
                }
            });

            bar.setAttribute('data-pinned-id', message.id);
            senderNameEl.innerText = (message.user ? message.user.name : '');

            let previewText = message.body || '';
            if (message.type === 'image') previewText = '[Hình ảnh]' + (message.body ? ': ' + message.body : '');
            else if (message.type === 'audio') previewText = '[Tin nhắn thoại]';
            else if (message.type === 'quiz') previewText = '[Bài kiểm tra]: ' + (message.body || '');
            else if (message.type === 'game_dice') previewText = '[Tung xúc xắc]';
            else if (message.type === 'game_rps') previewText = '[Oẳn tù tì]';
            else if (message.type === 'event') previewText = '[Lịch hẹn]: ' + (message.body || '');

            previewEl.innerText = previewText;
            bar.classList.remove('hidden');
            bar.classList.add('flex');
        } else {
            const currentPinnedId = parseInt(bar.getAttribute('data-pinned-id'));
            if (currentPinnedId === message.id) {
                bar.setAttribute('data-pinned-id', '0');
                bar.classList.add('hidden');
                bar.classList.remove('flex');
            }
        }

        lucide.createIcons();
    }

    // Event Listeners (Echo Realtime & Click Outside)
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('chat-messages-container');
        if (container) {
            hasMoreOlderMessages = container.getAttribute('data-has-more') === '1';
            oldestMessageId = parseInt(container.getAttribute('data-oldest-id')) || 0;
        }

        smartScrollToBottom(true, false);

        // Khoi tao quyen thong bao trinh duyet neu chua hoi
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }

        // Khoi chay bo dem nguoc thoi gian cho lich hen
        updateReminderBannerCountdown();
        setInterval(updateReminderBannerCountdown, 1000);

        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('conversation.{{ $activeConversation?->id ?? 0 }}')
                .listen('.MessageSent', (e) => {
                    hideTypingIndicator();
                    appendMessageToChat(e.message);
                    if (e.message.type === 'event') {
                        setUpcomingReminderBanner(e.message);
                    }
                })
                .listen('.MessageUpdated', (e) => {
                    updateMessageInChat(e.message);
                    handleMessagePinnedUpdated(e.message);
                    if (e.message.type === 'event') {
                        setUpcomingReminderBanner(e.message);
                    }
                })
                .listenForWhisper('typing', (e) => {
                    if (e.user_id !== {{ Auth::id() }}) {
                        showTypingIndicator(e.user_name);
                    }
                });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.reaction-picker-wrap')) {
                document.querySelectorAll('.reaction-popup').forEach(el => {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                });
            }
            if (!e.target.closest('#chat-search-panel') && !e.target.closest('button[onclick*="toggleChatSearch"]')) {
                const searchPanel = document.getElementById('chat-search-panel');
                if (searchPanel && !searchPanel.classList.contains('hidden')) {
                    closeChatSearch();
                }
            }
        });
    });
@endif
</script>