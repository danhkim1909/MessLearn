with open('resources/views/user/pages/chatboard/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

start_marker = "        async function sendChatMessage(e) {"
end_marker = "    @endif\n\n    let questionCount = 0;"

start_idx = content.find(start_marker)
end_idx = content.find(end_marker)

if start_idx != -1 and end_idx != -1:
    original = content[start_idx:end_idx]
    
    replacement = """        function appendMessageToChat(message) {
            const chatContainer = document.getElementById('chat-messages-container');
            if(!chatContainer) return;
            const isMine = message.user_id === {{ Auth::id() }};
            const avatarChar = message.user.name.charAt(0).toUpperCase();

            let innerContent = message.body;
            if (message.type === 'quiz') {
                innerContent = `
                    <div class="flex flex-col gap-2 ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-200'}">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <i data-lucide="help-circle" class="w-4 h-4"></i>
                            Bài kiểm tra
                        </div>
                        <p class="font-medium text-sm">${message.body}</p>
                        <button type="button" onclick="openQuizRunner(${message.form_id || '{{ $message->form_id ?? 0 }}' })" class="mt-2 w-full text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all ${isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white' }">
                            Bắt đầu làm bài
                        </button>
                    </div>
                `;
            } else {
                innerContent = innerContent.replace(/\\n/g, "<br>");
            }

            const messageHtml = `
                <div class="flex ${isMine ? 'justify-end' : 'justify-start'}">
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
                            <div class="${isMine ? 'bg-sky-500 text-white rounded-tr-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-sm'} px-4 py-2.5 rounded-2xl text-xs max-w-md">
                                ${innerContent}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            chatContainer.insertAdjacentHTML('beforeend', messageHtml);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            lucide.createIcons();
        }

        async function sendChatMessage(e) {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            
            if (!text) return;

            input.value = '';
            
            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                
                if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }

                const res = await fetch('{{ route('app.conversation.message.store', $activeConversation->id) }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ body: text })
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

        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('conversation.{{ $activeConversation->id }}')
                .listen('.MessageSent', (e) => {
                    appendMessageToChat(e.message);
                });
        }
"""
    new_content = content[:start_idx] + replacement + content[end_idx:]
    with open('resources/views/user/pages/chatboard/index.blade.php', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Replaced successfully")
else:
    print(f"Start index: {start_idx}, End index: {end_idx}")
