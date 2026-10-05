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
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/messages/load-more?before_id=${beforeId}&limit=25`);
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

    // Event Listeners (Echo Realtime & Click Outside)
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('chat-messages-container');
        if (container) {
            hasMoreOlderMessages = container.getAttribute('data-has-more') === '1';
            oldestMessageId = parseInt(container.getAttribute('data-oldest-id')) || 0;
        }

        smartScrollToBottom(true, false);

        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('conversation.{{ $activeConversation->id }}')
                .listen('.MessageSent', (e) => {
                    appendMessageToChat(e.message);
                })
                .listen('.MessageUpdated', (e) => {
                    updateMessageInChat(e.message);
                });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.reaction-picker-wrap')) {
                document.querySelectorAll('.reaction-popup').forEach(el => {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                });
            }
        });
    });
@endif
</script>