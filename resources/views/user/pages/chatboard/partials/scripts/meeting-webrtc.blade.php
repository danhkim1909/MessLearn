<script>
// --- WebRTC Online Meeting & Screen Sharing Engine ---
(function() {
    // Trạng thái cuộc gọi
    let activeMeetingId = null;
    let activeRoomCode = null;
    let activeCallType = 'video';
    let localStream = null;
    let screenStream = null;
    let peerConnection = null;
    let callTimerInterval = null;
    let callDurationSeconds = 0;
    let isMutedAudio = false;
    let isMutedVideo = false;
    let isSharingScreen = false;
    let incomingCallData = null;
    let isInitiator = false;

    // Web Audio Ringtone Synth
    let ringtoneAudioContext = null;
    let ringtoneInterval = null;

    const currentConvId = {{ $activeConversation?->id ?? 0 }};
    const currentUserId = {{ Auth::id() }};
    const currentUserName = '{{ addslashes(Auth::user()->name) }}';

    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' }
        ]
    };

    // --- Am thanh chuong reo bang Web Audio API ---
    function playTone(freq1, freq2, duration) {
        try {
            if (!ringtoneAudioContext) {
                ringtoneAudioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (ringtoneAudioContext.state === 'suspended') {
                ringtoneAudioContext.resume();
            }

            const osc1 = ringtoneAudioContext.createOscillator();
            const osc2 = ringtoneAudioContext.createOscillator();
            const gain = ringtoneAudioContext.createGain();

            osc1.type = 'sine';
            osc2.type = 'sine';
            osc1.frequency.value = freq1;
            osc2.frequency.value = freq2;

            gain.gain.setValueAtTime(0.1, ringtoneAudioContext.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ringtoneAudioContext.currentTime + duration);

            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(ringtoneAudioContext.destination);

            osc1.start();
            osc2.start();
            osc1.stop(ringtoneAudioContext.currentTime + duration);
            osc2.stop(ringtoneAudioContext.currentTime + duration);
        } catch (e) {
            // Trinh duyet chan autoplay audio
        }
    }

    function startIncomingRingtone() {
        stopRingtone();
        playTone(440, 480, 1.2);
        ringtoneInterval = setInterval(() => {
            playTone(440, 480, 1.2);
        }, 2500);
    }

    function startOutgoingRingtone() {
        stopRingtone();
        playTone(425, 425, 1.0);
        ringtoneInterval = setInterval(() => {
            playTone(425, 425, 1.0);
        }, 3000);
    }

    function stopRingtone() {
        if (ringtoneInterval) {
            clearInterval(ringtoneInterval);
            ringtoneInterval = null;
        }
    }

    // --- Dong ho dem thoi luong cuoc goi ---
    function startCallTimer() {
        stopCallTimer();
        callDurationSeconds = 0;
        const timerEl = document.getElementById('meeting-timer');
        if (timerEl) timerEl.innerText = '00:00';

        callTimerInterval = setInterval(() => {
            callDurationSeconds++;
            const mins = String(Math.floor(callDurationSeconds / 60)).padStart(2, '0');
            const secs = String(callDurationSeconds % 60).padStart(2, '0');
            if (timerEl) timerEl.innerText = `${mins}:${secs}`;
        }, 1000);
    }

    function stopCallTimer() {
        if (callTimerInterval) {
            clearInterval(callTimerInterval);
            callTimerInterval = null;
        }
    }

    // --- Khoi tao cuoc goi tu Header ---
    window.startCall = async function(type) {
        if (!currentConvId) return;

        activeCallType = type;
        isInitiator = true;

        try {
            // Yeu cau quyen truy cap Micro / Camera
            const constraints = {
                audio: true,
                video: type === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
            };

            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            isMutedAudio = false;
            isMutedVideo = type !== 'video';

            // Hien thi modal phong hop
            openMeetingRoomModal();
            attachLocalMediaStream(localStream);
            updateMediaControlsUI();

            const statusBadge = document.getElementById('meeting-status-badge');
            if (statusBadge) {
                statusBadge.innerText = 'Dang do chuong...';
                statusBadge.className = 'text-amber-400 font-medium';
            }

            startOutgoingRingtone();

            // Goi API khoi tao meeting
            const res = await fetch(`/app/conversation/${currentConvId}/meeting/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ type: type })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                activeMeetingId = data.meeting.id;
                activeRoomCode = data.room_code;
                initPeerConnection();
            } else {
                Toastify({ text: data.message || 'Khong the khoi tao cuoc goi', style: { background: '#f43f5e' } }).showToast();
                endCurrentCall();
            }
        } catch (err) {
            console.error('Loi truy cap camera/micro:', err);
            Toastify({ text: 'Vui long cap quyen Camera va Micro de bat dau cuoc goi.', style: { background: '#f43f5e' } }).showToast();
            cleanupCall();
        }
    };

    // --- Xu ly khi co cuoc goi den ---
    function handleIncomingCall(data) {
        incomingCallData = data;
        activeRoomCode = data.roomCode;
        activeCallType = data.callType;

        const nameEl = document.getElementById('incoming-call-name');
        const typeLabel = document.getElementById('incoming-call-type-label');
        const avatarEl = document.getElementById('incoming-call-avatar');

        if (nameEl) nameEl.innerText = data.senderName;
        if (typeLabel) {
            typeLabel.innerText = data.callType === 'voice' 
                ? 'Cuoc goi thoai dang den...' 
                : 'Cuoc goi video dang den...';
        }

        if (avatarEl) {
            if (data.senderAvatar) {
                avatarEl.innerHTML = `<img src="${data.senderAvatar}" class="w-full h-full object-cover" alt="${data.senderName}">`;
            } else {
                avatarEl.innerText = data.senderName ? data.senderName.charAt(0).toUpperCase() : 'U';
            }
        }

        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.remove('hidden');

        startIncomingRingtone();
    }

    // --- Chap nhan cuoc goi den ---
    window.acceptIncomingCall = async function() {
        stopRingtone();
        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.add('hidden');

        if (!incomingCallData) return;

        isInitiator = false;
        try {
            const constraints = {
                audio: true,
                video: activeCallType === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
            };

            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            isMutedAudio = false;
            isMutedVideo = activeCallType !== 'video';

            openMeetingRoomModal();
            attachLocalMediaStream(localStream);
            updateMediaControlsUI();

            const statusBadge = document.getElementById('meeting-status-badge');
            if (statusBadge) {
                statusBadge.innerText = 'Dang ket noi...';
                statusBadge.className = 'text-emerald-400 font-medium';
            }

            initPeerConnection();

            // Gui tin hieu accept_call
            await sendSignal('accept_call', { accepted: true });
            startCallTimer();
        } catch (err) {
            console.error('Loi chap nhan cuoc goi:', err);
            Toastify({ text: 'Khong the truy cap thiet bi micro/camera.', style: { background: '#f43f5e' } }).showToast();
            rejectIncomingCall();
        }
    };

    // --- Tu choi cuoc goi den ---
    window.rejectIncomingCall = async function() {
        stopRingtone();
        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.add('hidden');

        if (incomingCallData) {
            await fetch(`/app/conversation/${currentConvId}/meeting/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    room_code: incomingCallData.roomCode,
                    call_type: incomingCallData.callType
                })
            });
        }
        incomingCallData = null;
    };

    // --- Khoi tao WebRTC Peer Connection ---
    function initPeerConnection() {
        if (peerConnection) {
            peerConnection.close();
        }

        peerConnection = new RTCPeerConnection(rtcConfig);

        // Them cac track tu local stream vao ket noi
        if (localStream) {
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
        }

        // Lang nghe Remote Track den tu doi phuong
        peerConnection.ontrack = (event) => {
            const remoteVideo = document.getElementById('remote-video');
            const remoteFallback = document.getElementById('remote-video-fallback');

            if (remoteVideo && event.streams[0]) {
                remoteVideo.srcObject = event.streams[0];
                remoteVideo.classList.remove('hidden');
                if (remoteFallback) remoteFallback.classList.add('hidden');
            }

            const statusBadge = document.getElementById('meeting-status-badge');
            if (statusBadge) {
                statusBadge.innerText = 'Da ket noi';
                statusBadge.className = 'text-emerald-400 font-medium';
            }
        };

        // Lang nghe ICE Candidate va gui sang doi phuong
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal('webrtc_ice_candidate', { candidate: event.candidate });
            }
        };

        peerConnection.onconnectionstatechange = () => {
            if (!peerConnection) return;
            const state = peerConnection.connectionState;
            const statusBadge = document.getElementById('meeting-status-badge');

            if (state === 'connected') {
                if (statusBadge) {
                    statusBadge.innerText = 'Da ket noi';
                    statusBadge.className = 'text-emerald-400 font-medium';
                }
            } else if (state === 'disconnected' || state === 'failed') {
                if (statusBadge) {
                    statusBadge.innerText = 'Mat ket noi';
                    statusBadge.className = 'text-rose-400 font-medium';
                }
            }
        };

        // Neu la nguoi khoi tao, tao Offer
        if (isInitiator) {
            createAndSendOffer();
        }
    }

    async function createAndSendOffer() {
        if (!peerConnection) return;
        try {
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            await sendSignal('webrtc_offer', { sdp: peerConnection.localDescription });
        } catch (err) {
            console.error('Loi tao Offer:', err);
        }
    }

    async function handleReceiveOffer(sdp) {
        if (!peerConnection) {
            initPeerConnection();
        }
        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(sdp));
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);
            await sendSignal('webrtc_answer', { sdp: peerConnection.localDescription });
        } catch (err) {
            console.error('Loi xu ly Offer:', err);
        }
    }

    async function handleReceiveAnswer(sdp) {
        if (!peerConnection) return;
        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(sdp));
        } catch (err) {
            console.error('Loi xu ly Answer:', err);
        }
    }

    async function handleReceiveIceCandidate(candidate) {
        if (!peerConnection) return;
        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (err) {
            console.error('Loi them ICE Candidate:', err);
        }
    }

    // --- Gui tin hieu Signaling qua Reverb ---
    async function sendSignal(action, payload = {}) {
        try {
            await fetch(`/app/conversation/${currentConvId}/meeting/signal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: action,
                    room_code: activeRoomCode,
                    call_type: activeCallType,
                    payload: payload
                })
            });
        } catch (err) {
            console.error('Loi gui tin hieu signal:', err);
        }
    }

    // --- Dieu khien Micro / Camera ---
    window.toggleMicrophone = function() {
        if (!localStream) return;
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            isMutedAudio = !isMutedAudio;
            audioTrack.enabled = !isMutedAudio;
            updateMediaControlsUI();
            sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen });
        }
    };

    window.toggleCamera = function() {
        if (!localStream) return;
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            isMutedVideo = !isMutedVideo;
            videoTrack.enabled = !isMutedVideo;
            updateMediaControlsUI();
            sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen });
        }
    };

    // --- Chia se man hinh (Ke thua kien truc Nextcloud Talk) ---
    window.toggleScreenShare = async function() {
        if (isSharingScreen) {
            stopScreenShare();
        } else {
            startScreenShare();
        }
    };

    async function startScreenShare() {
        try {
            // Goi getDisplayMedia theo chuan Nextcloud Talk
            screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: true,
                audio: {
                    echoCancellation: false,
                    autoGainControl: false,
                    noiseSuppression: false
                }
            });

            isSharingScreen = true;
            const screenTrack = screenStream.getVideoTracks()[0];

            // Trao doi track camera bang track man hinh tren WebRTC Sender
            if (peerConnection) {
                const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender) {
                    sender.replaceTrack(screenTrack);
                }
            }

            // Hien thi Spotlight man hinh chieu tren UI
            const spotlightContainer = document.getElementById('screen-share-spotlight');
            const screenVideo = document.getElementById('screen-share-video');
            const mirrorPlaceholder = document.getElementById('screen-mirror-placeholder');
            const screenSharerName = document.getElementById('screen-sharer-name');

            if (spotlightContainer) spotlightContainer.classList.remove('hidden');
            if (screenVideo) screenVideo.srcObject = screenStream;
            if (screenSharerName) screenSharerName.innerText = 'Man hinh cua ban';

            // Kiem tra loai display surface de chong hieu ung guong vo tan
            const displaySurface = screenTrack.getSettings?.().displaySurface;
            if (displaySurface !== 'browser' && mirrorPlaceholder) {
                mirrorPlaceholder.classList.remove('hidden');
            }

            // Lang nghe khi nguoi dung bam nut Stop Sharing tren trinh duyet
            screenTrack.onended = () => {
                stopScreenShare();
            };

            updateMediaControlsUI();
            sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen: true });
        } catch (err) {
            console.error('Loi chia se man hinh:', err);
            isSharingScreen = false;
        }
    }

    window.stopScreenShare = function() {
        if (screenStream) {
            screenStream.getTracks().forEach(t => t.stop());
            screenStream = null;
        }

        isSharingScreen = false;

        // Trao nguoc lai track camera cu
        if (peerConnection && localStream) {
            const videoTrack = localStream.getVideoTracks()[0];
            const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'video');
            if (sender && videoTrack) {
                sender.replaceTrack(videoTrack);
            }
        }

        // An Spotlight man hinh chieu
        const spotlightContainer = document.getElementById('screen-share-spotlight');
        const mirrorPlaceholder = document.getElementById('screen-mirror-placeholder');
        if (spotlightContainer) spotlightContainer.classList.add('hidden');
        if (mirrorPlaceholder) mirrorPlaceholder.classList.add('hidden');

        updateMediaControlsUI();
        sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen: false });
    };

    // --- Ket thuc cuoc goi / Roi phong ---
    window.endCurrentCall = async function() {
        stopRingtone();
        stopCallTimer();

        if (activeRoomCode) {
            try {
                await fetch(`/app/conversation/${currentConvId}/meeting/leave`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        room_code: activeRoomCode,
                        call_type: activeCallType
                    })
                });
            } catch (err) {
                console.error('Loi leave call:', err);
            }
        }

        cleanupCall();
    };

    function cleanupCall() {
        stopRingtone();
        stopCallTimer();

        if (localStream) {
            localStream.getTracks().forEach(t => t.stop());
            localStream = null;
        }

        if (screenStream) {
            screenStream.getTracks().forEach(t => t.stop());
            screenStream = null;
        }

        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }

        activeMeetingId = null;
        activeRoomCode = null;
        isSharingScreen = false;
        isInitiator = false;

        const modalMeeting = document.getElementById('modal-meeting-room');
        if (modalMeeting) modalMeeting.classList.add('hidden');

        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.add('hidden');
    }

    // --- Cap nhat giao dien ---
    function openMeetingRoomModal() {
        const modalMeeting = document.getElementById('modal-meeting-room');
        if (modalMeeting) modalMeeting.classList.remove('hidden');

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    function attachLocalMediaStream(stream) {
        const localVideo = document.getElementById('local-video');
        const localFallback = document.getElementById('local-video-fallback');

        if (localVideo) {
            localVideo.srcObject = stream;
            if (activeCallType === 'voice') {
                localVideo.classList.add('hidden');
                if (localFallback) localFallback.classList.remove('hidden');
            } else {
                localVideo.classList.remove('hidden');
                if (localFallback) localFallback.classList.add('hidden');
            }
        }
    }

    function updateMediaControlsUI() {
        // Nut Mic
        const btnMic = document.getElementById('btn-call-mic');
        const iconMic = document.getElementById('icon-call-mic');
        const localMicBadge = document.getElementById('local-mic-badge');

        if (btnMic && iconMic) {
            if (isMutedAudio) {
                btnMic.className = 'w-11 h-11 rounded-full bg-rose-600 text-white flex items-center justify-center transition-all active:scale-95';
                iconMic.setAttribute('data-lucide', 'mic-off');
                if (localMicBadge) localMicBadge.className = 'text-rose-400';
            } else {
                btnMic.className = 'w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95';
                iconMic.setAttribute('data-lucide', 'mic');
                if (localMicBadge) localMicBadge.className = 'text-emerald-400';
            }
        }

        // Nut Camera
        const btnCam = document.getElementById('btn-call-cam');
        const iconCam = document.getElementById('icon-call-cam');
        const localVideo = document.getElementById('local-video');
        const localFallback = document.getElementById('local-video-fallback');

        if (btnCam && iconCam) {
            if (isMutedVideo) {
                btnCam.className = 'w-11 h-11 rounded-full bg-rose-600 text-white flex items-center justify-center transition-all active:scale-95';
                iconCam.setAttribute('data-lucide', 'video-off');
                if (localVideo) localVideo.classList.add('hidden');
                if (localFallback) localFallback.classList.remove('hidden');
            } else {
                btnCam.className = 'w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95';
                iconCam.setAttribute('data-lucide', 'video');
                if (localVideo) localVideo.classList.remove('hidden');
                if (localFallback) localFallback.classList.add('hidden');
            }
        }

        // Nut Chia se man hinh
        const btnScreen = document.getElementById('btn-call-screen');
        if (btnScreen) {
            if (isSharingScreen) {
                btnScreen.className = 'w-11 h-11 rounded-full bg-sky-500 text-white flex items-center justify-center transition-all active:scale-95';
            } else {
                btnScreen.className = 'w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95';
            }
        }

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    window.toggleMeetingFullscreen = function() {
        const modal = document.getElementById('modal-meeting-room');
        if (!document.fullscreenElement) {
            if (modal.requestFullscreen) {
                modal.requestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    };

    // --- Lang nghe su kien qua Laravel Reverb ---
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.Echo === 'undefined' || !currentConvId) return;

        window.Echo.private(`conversation.${currentConvId}`)
            .listen('.CallSignalEvent', (e) => {
                if (e.senderId === currentUserId) return;

                switch (e.action) {
                    case 'incoming_call':
                        handleIncomingCall(e);
                        break;
                    case 'accept_call':
                        stopRingtone();
                        startCallTimer();
                        const statusBadge = document.getElementById('meeting-status-badge');
                        if (statusBadge) {
                            statusBadge.innerText = 'Da ket noi';
                            statusBadge.className = 'text-emerald-400 font-medium';
                        }
                        if (isInitiator) {
                            createAndSendOffer();
                        }
                        break;
                    case 'reject_call':
                        stopRingtone();
                        Toastify({ text: 'Doi phuong da tu choi cuoc goi.', style: { background: '#f43f5e' } }).showToast();
                        cleanupCall();
                        break;
                    case 'end_call':
                        Toastify({ text: 'Cuoc goi da ket thuc.', style: { background: '#64748b' } }).showToast();
                        cleanupCall();
                        break;
                    case 'webrtc_offer':
                        handleReceiveOffer(e.payload.sdp);
                        break;
                    case 'webrtc_answer':
                        handleReceiveAnswer(e.payload.sdp);
                        break;
                    case 'webrtc_ice_candidate':
                        handleReceiveIceCandidate(e.payload.candidate);
                        break;
                    case 'media_state_changed':
                        const remoteMic = document.getElementById('remote-mic-badge');
                        const remoteVideo = document.getElementById('remote-video');
                        const remoteFallback = document.getElementById('remote-video-fallback');

                        if (remoteMic) {
                            remoteMic.className = e.payload.isMutedAudio ? 'text-rose-400' : 'text-emerald-400';
                        }
                        if (remoteVideo && remoteFallback) {
                            if (e.payload.isMutedVideo) {
                                remoteVideo.classList.add('hidden');
                                remoteFallback.classList.remove('hidden');
                            } else {
                                remoteVideo.classList.remove('hidden');
                                remoteFallback.classList.add('hidden');
                            }
                        }
                        break;
                }
            });
    });
})();
</script>
