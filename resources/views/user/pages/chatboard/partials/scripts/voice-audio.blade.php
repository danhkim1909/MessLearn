<script>
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
</script>
