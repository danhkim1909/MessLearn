import re

with open('resources/views/user/pages/chatboard/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Insert prepareReply and cancelReply before sendChatMessage
js_functions = """
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
        
        async function sendChatMessage(e) {"""

content = content.replace("        async function sendChatMessage(e) {", js_functions)

# 2. Modify sendChatMessage to read reply_to_id and clear it
old_send_logic = """            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            
            if (!text) return;

            input.value = '';
            input.style.height = 'auto'; // Reset textarea height
            input.focus();

            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };

            try {
                const res = await fetch('{{ route('app.conversation.message.store', $activeConversation->id) }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ body: text })
                });"""

new_send_logic = """            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            const replyToId = document.getElementById('reply-to-id').value;
            
            if (!text) return;

            input.value = '';
            input.style.height = 'auto'; // Reset textarea height
            cancelReply();
            input.focus();

            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            
            const payload = { body: text };
            if (replyToId) payload.reply_to_id = replyToId;

            try {
                const res = await fetch('{{ route('app.conversation.message.store', $activeConversation->id) }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                });"""
content = content.replace(old_send_logic, new_send_logic)

with open('resources/views/user/pages/chatboard/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated sendChatMessage and added reply JS functions")
