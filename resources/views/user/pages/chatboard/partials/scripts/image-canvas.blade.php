<script>
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

            const res = await fetch('{{ route('app.conversation.message.store', $activeConversation?->id ?? 0) }}', {
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
</script>
