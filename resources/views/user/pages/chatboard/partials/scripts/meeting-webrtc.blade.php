<script>
// -------------------------------------------------------------
// WEBRTC ONLINE MEETING & SCREEN SHARING ENGINE (MESSCALL)
// HE THONG CUOC GOI VA PHONG HOC NHOM WEBRTC FULL-MESH
// -------------------------------------------------------------
(function() {
    // Trang thai cuoc goi va phong hop
    let activeMeetingId = null;
    let activeRoomCode = null;
    let activeCallType = 'video';
    let activeCallMode = 'call'; // 'call' (do chuong) hoac 'classroom' (phong hoc lobby/banner)
    let localStream = null;

    // Kien truc WebRTC Full-Mesh
    let peers = {}; // key: remoteUserId -> RTCPeerConnection
    let remoteStreams = {}; // key: remoteUserId -> MediaStream
    window.peers = peers;
    window.remoteStreams = remoteStreams;
    window.getWebrtcStatus = () => ({
        roomCode: activeRoomCode,
        callType: activeCallType,
        hasLocalStream: !!localStream,
        peers: Object.keys(peers).map(id => ({
            id: Number(id),
            connectionState: peers[id].connectionState,
            iceConnectionState: peers[id].iceConnectionState,
            signalingState: peers[id].signalingState
        })),
        remoteStreams: Object.keys(remoteStreams).map(id => ({
            id: Number(id),
            tracks: remoteStreams[id].getTracks().map(t => `${t.kind}:${t.readyState}:${t.enabled}`)
        }))
    });
    let remoteUserProfiles = {}; // key: remoteUserId -> { name, avatar }
    let remoteUserStates = {}; // key: remoteUserId -> { isMutedAudio, isMutedVideo, isHandRaised, isSharingScreen }
    let pendingIceCandidates = {}; // key: remoteUserId -> Array[RTCIceCandidate]

    let screenStream = null;
    let callTimerInterval = null;
    let ringTimeoutTimer = null;
    let callDurationSeconds = 0;
    let isMutedAudio = false;
    let isMutedVideo = false;
    let isSharingScreen = false;
    let currentScreenSharerId = null;
    let currentScreenSharerName = '';
    let wasCameraActiveBeforeScreenShare = false;
    let incomingCallData = null;
    let isInitiator = false;

    // Trang thai phong hop nhom va phan quyen Host
    let isGroupMeeting = false;
    let isMeetingHost = false;
    let currentHostUserId = null;
    let isHandRaised = false;

    // Trang thai tuy chon tien cuoc goi (Pre-call Controls)
    let preCallMutedAudio = false;
    let preCallMutedVideo = false;

    // Trang thai phong cho phong hoc nhom (Meeting Lobby)
    let lobbyPreviewStream = null;
    let lobbyMutedAudio = false;
    let lobbyMutedVideo = false;
    let lobbyTargetCallType = 'video';

    // Web Audio Ringtone Synth
    let ringtoneAudioContext = null;
    let ringtoneInterval = null;

    let peerDisconnectTimers = {};
    let incomingCallRingTimeoutTimer = null;
    const pageConversationId = {{ $activeConversation?->id ?? 'null' }};
    let subscribedConversationChannels = new Set();

    let activeConversationId = {{ $activeConversation?->id ?? 'null' }};
    const currentUserId = {{ Auth::id() }};
    const currentUserName = '{{ addslashes(Auth::user()->name) }}';
    const currentUserAvatar = '{{ Auth::user()->avatar_url ?? "" }}';

    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' }
        ],
        sdpSemantics: 'unified-plan'
    };

    function ensureConversationEchoSubscribed(convId) {
        if (!convId || typeof window.Echo === 'undefined') return;
        const convIdNum = Number(convId);
        if (subscribedConversationChannels.has(convIdNum)) return;
        subscribedConversationChannels.add(convIdNum);
        window.Echo.private(`conversation.${convIdNum}`)
            .listen('.CallSignalEvent', handleCallSignal);
    }

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
            // Trinh duyet chan autoplay audio neu chua co tuong tac
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
        if (ringTimeoutTimer) {
            clearTimeout(ringTimeoutTimer);
            ringTimeoutTimer = null;
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

    // --- Xu ly khi goi khong ai nghe may (45 giay timeout) ---
    async function handleCallTimeout() {
        stopRingtone();
        if (typeof Toastify === 'function') {
            Toastify({
                text: 'Nguoi nhan hien khong tra loi cuoc goi.',
                duration: 4000,
                style: { background: '#f43f5e', borderRadius: '0.5rem' }
            }).showToast();
        }
        await endCurrentCall();
    }

    // --- Khoi tao cuoc goi tu Header hoac tham gia tu Lobby ---
    window.startCall = async function(type, initialMediaOptions = null, callMode = 'call') {
        if (!activeConversationId) {
            if (typeof Toastify === 'function') {
                Toastify({ text: 'Vui long chon mot cuoc tro chuyen de goi.', style: { background: '#f43f5e' } }).showToast();
            }
            return;
        }

        activeCallType = type;
        activeCallMode = callMode || 'call';
        isInitiator = true;
        ensureConversationEchoSubscribed(activeConversationId);

        try {
            // Yeu cau quyen truy cap Micro va Camera
            const constraints = {
                audio: true,
                video: type === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
            };

            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            
            // Ap dung tuy chon Mic/Cam neu co truyen tu Phong cho (Meeting Lobby)
            if (initialMediaOptions) {
                isMutedAudio = !!initialMediaOptions.mutedAudio;
                isMutedVideo = type === 'video' ? !!initialMediaOptions.mutedVideo : true;
            } else {
                isMutedAudio = false;
                isMutedVideo = type !== 'video';
            }

            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !isMutedAudio;
            }
            const videoTrack = localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !isMutedVideo;
            }

            // Hien thi modal phong hop
            openMeetingRoomModal();
            attachLocalMediaStream(localStream);
            updateMediaControlsUI();
            updateAdaptiveVideoGrid();

            const meetingTitleEl = document.getElementById('meeting-room-title');
            if (meetingTitleEl) {
                if (activeCallMode === 'classroom') {
                    meetingTitleEl.innerText = 'Phòng học nhóm trực tuyến';
                } else {
                    meetingTitleEl.innerText = (activeCallType === 'voice') ? 'Cuộc gọi thoại' : 'Cuộc gọi video';
                }
            }

            const statusBadge = document.getElementById('meeting-status-badge');
            if (statusBadge) {
                statusBadge.innerText = (activeCallMode === 'classroom') ? 'Đang diễn ra' : 'Đang đổ chuông...';
                statusBadge.className = (activeCallMode === 'classroom') ? 'text-emerald-400 font-medium' : 'text-amber-400 font-medium';
            }

            if (activeCallMode !== 'classroom') {
                startOutgoingRingtone();
                ringTimeoutTimer = setTimeout(handleCallTimeout, 45000);
            } else {
                startCallTimer();
            }

            // Goi API khoi tao hoac tham gia meeting tren backend
            const res = await fetch(`/app/conversation/${activeConversationId}/meeting/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ type: type, mode: activeCallMode })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                activeMeetingId = data.meeting?.id;
                activeRoomCode = data.room_code;
                isGroupMeeting = !!data.is_group;
                activeCallMode = data.mode || activeCallMode;
                isMeetingHost = !!data.is_host;
                currentHostUserId = data.host_id || null;

                if (isGroupMeeting && activeCallMode === 'classroom') {
                    stopRingtone();
                    clearTimeout(ringTimeoutTimer);
                    startCallTimer();
                }

                updateMeetingRoleUI();

                // Ket noi Mesh voi tat ca thanh vien dang co san trong phong
                if (data.existing_participants && Array.isArray(data.existing_participants)) {
                    data.existing_participants.forEach(p => {
                        initiateConnectionWithPeer(p.id, { name: p.name, avatar: p.avatar });
                    });
                }
            } else {
                if (typeof Toastify === 'function') {
                    Toastify({ text: data.message || 'Khong the khoi tao cuoc goi.', style: { background: '#f43f5e' } }).showToast();
                }
                cleanupCall();
            }
        } catch (err) {
            console.error('Loi truy cap camera/micro:', err);
            if (typeof Toastify === 'function') {
                Toastify({ text: 'Vui long cap quyen Camera va Micro de bat dau cuoc goi.', style: { background: '#f43f5e' } }).showToast();
            }
            cleanupCall();
        }
    };

    // --- Cap nhat giao dien vai tro (Host vs Member) va Cuoc goi 1-1 ---
    function updateMeetingRoleUI() {
        const roleBadge = document.getElementById('meeting-user-role-badge');
        const roleText = document.getElementById('meeting-role-text');
        const localCrown = document.getElementById('local-crown-badge');
        const localHand = document.getElementById('local-hand-badge');
        const btnHand = document.getElementById('btn-call-hand');
        const btnHostMuteAll = document.getElementById('btn-host-mute-all');
        const btnEndText = document.getElementById('btn-call-end-text');

        if (isGroupMeeting) {
            // Phong hop nhom truc tuyen
            if (roleBadge) {
                roleBadge.classList.remove('hidden');
                roleBadge.classList.add('inline-flex');
                if (isMeetingHost) {
                    roleBadge.className = 'inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30';
                    if (roleText) roleText.innerText = 'Chủ phòng';
                } else {
                    roleBadge.className = 'inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700';
                    if (roleText) roleText.innerText = 'Thành viên';
                }
            }

            if (localCrown) {
                if (isMeetingHost) localCrown.classList.remove('hidden');
                else localCrown.classList.add('hidden');
            }

            if (btnHand) {
                btnHand.classList.remove('hidden');
                if (isHandRaised) {
                    btnHand.className = 'w-11 h-11 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center transition-all active:scale-95 shadow-lg shadow-amber-500/30';
                } else {
                    btnHand.className = 'w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95';
                }
            }

            if (localHand) {
                if (isHandRaised) localHand.classList.remove('hidden');
                else localHand.classList.add('hidden');
            }

            if (btnHostMuteAll) {
                if (isMeetingHost) btnHostMuteAll.classList.remove('hidden');
                else btnHostMuteAll.classList.add('hidden');
            }

            if (btnEndText) {
                btnEndText.innerText = isMeetingHost ? 'Rời / Đóng' : 'Rời phòng';
            }
        } else {
            // Cuoc goi ban be 1-1
            if (roleBadge) {
                roleBadge.classList.add('hidden');
                roleBadge.classList.remove('inline-flex');
            }
            if (localCrown) localCrown.classList.add('hidden');
            if (localHand) localHand.classList.add('hidden');
            if (btnHand) btnHand.classList.add('hidden');
            if (btnHostMuteAll) btnHostMuteAll.classList.add('hidden');
            if (btnEndText) btnEndText.innerText = 'Kết thúc';
        }

        // Cap nhat hien thi nut quan ly Host tren the cua cac thanh vien
        const hostActionButtons = document.querySelectorAll('[id^="remote-host-actions-"]');
        hostActionButtons.forEach(el => {
            el.classList.toggle('hidden', !(isGroupMeeting && isMeetingHost));
        });

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    // --- Don dep khung Spotlight chia se man hinh dung chung ---
    function teardownScreenShareSpotlight() {
        const prevSharerId = currentScreenSharerId;
        currentScreenSharerId = null;
        currentScreenSharerName = '';

        const spotlightContainer = document.getElementById('screen-share-spotlight');
        const mirrorPlaceholder = document.getElementById('screen-mirror-placeholder');
        const screenVideo = document.getElementById('screen-share-video');

        if (spotlightContainer) spotlightContainer.classList.add('hidden');
        if (mirrorPlaceholder) mirrorPlaceholder.classList.add('hidden');
        if (screenVideo) screenVideo.srcObject = null;

        // Khoi phuc the camera cua nguoi vua dung chia se
        if (prevSharerId && Number(prevSharerId) !== Number(currentUserId)) {
            renderOrUpdateParticipantCard(prevSharerId);
        }

        updateMediaControlsUI();
    }

    // --- Cap nhat luoi video thich ung (Adaptive Video Grid) ---
    function updateAdaptiveVideoGrid() {
        const gridEl = document.getElementById('meeting-video-grid');
        const waitingBadge = document.getElementById('waiting-peer-badge');
        if (!gridEl) return;

        const peerCount = Object.keys(peers).length;
        const totalCount = peerCount + 1; // 1 local card + so the remote

        // Reset tat ca class cot cu
        gridEl.classList.remove('grid-cols-1', 'grid-cols-2', 'md:grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-4');

        if (totalCount <= 1) {
            // Chi co 1 minh trong phong: Bung full 100% va hien badge cho
            gridEl.classList.add('grid-cols-1');
            if (waitingBadge) {
                waitingBadge.classList.remove('hidden');
                waitingBadge.classList.add('flex');
            }
        } else {
            // Da co thanh vien khac tham gia: An badge cho
            if (waitingBadge) {
                waitingBadge.classList.add('hidden');
                waitingBadge.classList.remove('flex');
            }

            if (totalCount === 2) {
                gridEl.classList.add('grid-cols-1', 'md:grid-cols-2');
            } else if (totalCount <= 4) {
                gridEl.classList.add('grid-cols-2', 'md:grid-cols-2');
            } else {
                gridEl.classList.add('grid-cols-2', 'md:grid-cols-3');
            }
        }
    }

    // --- Render hoac cap nhat the Camera cua tung thanh vien Remote trong Mesh ---
    function renderOrUpdateParticipantCard(userId) {
        const rId = Number(userId);
        if (!rId || rId === Number(currentUserId)) return;

        const gridEl = document.getElementById('meeting-video-grid');
        if (!gridEl) return;

        const profile = remoteUserProfiles[rId] || { name: 'Thành viên', avatar: null };
        const userState = remoteUserStates[rId] || { isMutedAudio: false, isMutedVideo: (activeCallType === 'voice') };
        const isHost = (currentHostUserId && Number(currentHostUserId) === rId);
        const isHand = !!userState.isHandRaised;
        const isMuted = !!userState.isMutedAudio;

        let card = document.getElementById('remote-card-' + rId);
        if (!card) {
            card = document.createElement('div');
            card.id = 'remote-card-' + rId;
            card.className = 'relative bg-slate-900/90 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-lg transition-all duration-300 min-h-[180px]';
            card.innerHTML = `
                <video id="remote-video-${rId}" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>
                <audio id="remote-audio-${rId}" autoplay playsinline class="hidden"></audio>
                
                <div id="remote-fallback-${rId}" class="flex flex-col items-center gap-3">
                    <div class="w-24 h-24 rounded-full bg-slate-800 border-2 border-slate-700 text-slate-200 flex items-center justify-center font-extrabold text-3xl shadow-inner overflow-hidden">
                        ${profile.avatar ? `<img src="${profile.avatar}" class="w-full h-full object-cover" alt="${profile.name}">` : `<span>${profile.name ? profile.name.charAt(0).toUpperCase() : 'U'}</span>`}
                    </div>
                    <p id="remote-name-label-${rId}" class="font-bold text-xs text-slate-300 text-center max-w-[200px] truncate">${profile.name} (Camera đang tắt)</p>
                </div>

                <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md text-white text-xs font-semibold px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-2 z-10">
                    <span id="remote-crown-${rId}" class="${isHost ? '' : 'hidden'} text-amber-400" title="Chủ phòng">
                        <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate max-w-[120px]">${profile.name}</span>
                    <span id="remote-hand-${rId}" class="${isHand ? '' : 'hidden'} text-amber-400 animate-bounce" title="Đang giơ tay phát biểu">
                        <i data-lucide="hand" class="w-3.5 h-3.5 fill-amber-400/30"></i>
                    </span>
                    <span id="remote-mic-${rId}" class="${isMuted ? 'text-rose-400' : 'text-emerald-400'}">
                        <i data-lucide="${isMuted ? 'mic-off' : 'mic'}" class="w-3.5 h-3.5"></i>
                    </span>
                </div>

                <div id="remote-host-actions-${rId}" class="${(isGroupMeeting && isMeetingHost) ? '' : 'hidden'} absolute top-3 right-3 z-10">
                    <button type="button" onclick="hostMuteRemoteParticipant(${rId})" 
                            class="p-2 rounded-xl bg-slate-800/80 hover:bg-rose-600/90 text-white backdrop-blur-md border border-slate-700 transition-all text-xs flex items-center gap-1 shadow-md"
                            title="Tắt micro thành viên này">
                        <i data-lucide="mic-off" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            `;
            gridEl.appendChild(card);
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        } else {
            // Cap nhat badge thong tin tren the da co
            const crownEl = document.getElementById('remote-crown-' + rId);
            if (crownEl) crownEl.classList.toggle('hidden', !isHost);

            const handEl = document.getElementById('remote-hand-' + rId);
            if (handEl) handEl.classList.toggle('hidden', !isHand);

            const micEl = document.getElementById('remote-mic-' + rId);
            if (micEl) {
                micEl.className = isMuted ? 'text-rose-400' : 'text-emerald-400';
                micEl.innerHTML = `<i data-lucide="${isMuted ? 'mic-off' : 'mic'}" class="w-3.5 h-3.5"></i>`;
            }

            const hostAction = document.getElementById('remote-host-actions-' + rId);
            if (hostAction) hostAction.classList.toggle('hidden', !(isGroupMeeting && isMeetingHost));

            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        }

        // Gan luong MediaStream vao the video va the audio doc lap
        const stream = remoteStreams[rId];
        const videoEl = document.getElementById('remote-video-' + rId);
        const audioEl = document.getElementById('remote-audio-' + rId);
        const fallbackEl = document.getElementById('remote-fallback-' + rId);

        if (audioEl && stream) {
            if (audioEl.srcObject !== stream) {
                audioEl.srcObject = stream;
            }
            audioEl.play().catch(e => console.warn('Audio play blocked:', e));
        }

        if (videoEl && fallbackEl) {
            // Neu thanh vien nay dang chia se man hinh vao Spotlight
            if (currentScreenSharerId && Number(currentScreenSharerId) === rId) {
                videoEl.classList.add('hidden');
                fallbackEl.classList.remove('hidden');
            } else {
                const hasLiveVideoTrack = stream && stream.getVideoTracks && stream.getVideoTracks().some(t => t.readyState === 'live');
                const isRemoteVideoAllowed = !userState.isMutedVideo;
                // Chi hien thi the video khi thuc su co track dang phat (live) va nguoi dung khong tat camera
                const shouldShowVideo = Boolean(stream && hasLiveVideoTrack && isRemoteVideoAllowed);

                if (shouldShowVideo) {
                    if (videoEl.srcObject !== stream) {
                        videoEl.srcObject = stream;
                    }
                    videoEl.muted = true;
                    videoEl.classList.remove('hidden');
                    fallbackEl.classList.add('hidden');

                    const playRemoteVideo = () => {
                        if (videoEl.paused) {
                            videoEl.play().catch(e => console.warn('Video play blocked:', e));
                        }
                    };
                    videoEl.onloadedmetadata = playRemoteVideo;
                    playRemoteVideo();
                } else {
                    videoEl.classList.add('hidden');
                    fallbackEl.classList.remove('hidden');
                    if (videoEl.srcObject) {
                        videoEl.pause();
                        videoEl.srcObject = null;
                    }
                }
            }
        }

        updateAdaptiveVideoGrid();
    }

    // --- Xoa thanh vien khoi luoi va don dep ket noi ---
    function removeParticipant(userId) {
        const rId = Number(userId);
        if (!rId) return;

        if (peerDisconnectTimers[rId]) {
            clearTimeout(peerDisconnectTimers[rId]);
            delete peerDisconnectTimers[rId];
        }

        if (peers[rId]) {
            peers[rId].close();
            delete peers[rId];
        }
        delete remoteStreams[rId];
        delete remoteUserProfiles[rId];
        delete pendingIceCandidates[rId];
        delete remoteUserStates[rId];

        const card = document.getElementById('remote-card-' + rId);
        if (card) {
            card.remove();
        }

        if (currentScreenSharerId && Number(currentScreenSharerId) === rId) {
            teardownScreenShareSpotlight();
        }

        updateAdaptiveVideoGrid();
    }

    // --- Gio tay / Ha tay phat bieu ---
    window.toggleRaiseHand = function() {
        if (!isGroupMeeting) return;
        isHandRaised = !isHandRaised;
        updateMeetingRoleUI();
        sendSignal(isHandRaised ? 'raise_hand' : 'lower_hand', {
            userId: currentUserId,
            userName: currentUserName
        });
        if (typeof Toastify === 'function') {
            Toastify({
                text: isHandRaised ? 'Bạn đã giơ tay phát biểu.' : 'Bạn đã hạ tay.',
                style: { background: isHandRaised ? '#f59e0b' : '#64748b', borderRadius: '0.5rem' },
                duration: 2500
            }).showToast();
        }
    };

    // --- Chu phong tat mic doi phuong ---
    window.hostMuteRemoteParticipant = function(targetUserId) {
        if (!isGroupMeeting || !isMeetingHost || !targetUserId) return;
        sendSignal('host_mute_user', {
            hostId: currentUserId,
            hostName: currentUserName
        }, targetUserId);
        if (typeof Toastify === 'function') {
            Toastify({
                text: 'Đã yêu cầu tắt micro thành viên.',
                style: { background: '#0284c7', borderRadius: '0.5rem' },
                duration: 2500
            }).showToast();
        }
    };

    // --- Chu phong tat mic ca phong ---
    window.hostMuteAllParticipants = function() {
        if (!isGroupMeeting || !isMeetingHost) return;
        sendSignal('host_mute_all', {
            hostId: currentUserId,
            hostName: currentUserName
        });
        if (typeof Toastify === 'function') {
            Toastify({
                text: 'Đã tắt micro toàn bộ thành viên trong phòng.',
                style: { background: '#0284c7', borderRadius: '0.5rem' },
                duration: 2500
            }).showToast();
        }
    };

    // --- Xu ly khi bam nut Do roi phong / ket thuc ---
    window.handleCallEndButtonClick = function() {
        if (isGroupMeeting && isMeetingHost) {
            const modalConfirm = document.getElementById('modal-host-leave-confirm');
            if (modalConfirm) {
                modalConfirm.classList.remove('hidden');
                modalConfirm.classList.add('flex');
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            }
        } else {
            endCurrentCall(false);
        }
    };

    window.closeHostLeaveConfirmModal = function() {
        const modalConfirm = document.getElementById('modal-host-leave-confirm');
        if (modalConfirm) {
            modalConfirm.classList.add('hidden');
            modalConfirm.classList.remove('flex');
        }
    };

    window.confirmHostLeave = async function(endForAll) {
        closeHostLeaveConfirmModal();
        await endCurrentCall(endForAll);
    };

    // --- PHONG CHO CHUAN BI PHONG HOC NHOM (MEETING LOBBY) ---
    window.openMeetingLobby = async function(type) {
        if (!activeConversationId) {
            if (typeof Toastify === 'function') {
                Toastify({ text: 'Vui long chon mot cuoc tro chuyen de vao phong.', style: { background: '#f43f5e' } }).showToast();
            }
            return;
        }

        lobbyTargetCallType = type;
        lobbyMutedAudio = false;
        lobbyMutedVideo = (type === 'voice');

        const modalLobby = document.getElementById('modal-meeting-lobby');
        const previewVideo = document.getElementById('lobby-preview-video');
        const fallback = document.getElementById('lobby-camera-fallback');
        const avatarLetter = document.getElementById('lobby-avatar-letter');
        const btnCam = document.getElementById('btn-lobby-cam');

        if (avatarLetter) {
            avatarLetter.innerText = currentUserName ? currentUserName.charAt(0).toUpperCase() : 'U';
        }

        if (modalLobby) {
            modalLobby.classList.remove('hidden');
        }

        if (type === 'voice') {
            if (btnCam) btnCam.classList.add('hidden');
            if (fallback) fallback.classList.remove('hidden');
            if (previewVideo) previewVideo.classList.add('hidden');
        } else {
            if (btnCam) btnCam.classList.remove('hidden');
            try {
                lobbyPreviewStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 360 } },
                    audio: false
                });
                if (previewVideo) {
                    previewVideo.srcObject = lobbyPreviewStream;
                    previewVideo.classList.remove('hidden');
                }
                if (fallback) fallback.classList.add('hidden');
            } catch (err) {
                console.warn('Khong the bat camera preview phong cho:', err);
                lobbyMutedVideo = true;
                if (previewVideo) previewVideo.classList.add('hidden');
                if (fallback) fallback.classList.remove('hidden');
            }
        }

        updateLobbyUI();
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    };

    window.toggleLobbyMic = function() {
        lobbyMutedAudio = !lobbyMutedAudio;
        updateLobbyUI();
    };

    window.toggleLobbyCam = function() {
        if (lobbyTargetCallType === 'voice') return;
        lobbyMutedVideo = !lobbyMutedVideo;

        const previewVideo = document.getElementById('lobby-preview-video');
        const fallback = document.getElementById('lobby-camera-fallback');

        if (lobbyPreviewStream) {
            const track = lobbyPreviewStream.getVideoTracks()[0];
            if (track) {
                track.enabled = !lobbyMutedVideo;
            }
        }

        if (lobbyMutedVideo) {
            if (previewVideo) previewVideo.classList.add('hidden');
            if (fallback) fallback.classList.remove('hidden');
        } else {
            if (previewVideo) previewVideo.classList.remove('hidden');
            if (fallback) fallback.classList.add('hidden');
        }

        updateLobbyUI();
    };

    function updateLobbyUI() {
        const btnMic = document.getElementById('btn-lobby-mic');
        const iconMic = document.getElementById('icon-lobby-mic');
        const textMic = document.getElementById('text-lobby-mic');
        const micBadgeIcon = document.getElementById('lobby-mic-badge-icon');
        const micBadgeText = document.getElementById('lobby-mic-badge-text');

        if (btnMic && iconMic && textMic) {
            if (lobbyMutedAudio) {
                btnMic.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all active:scale-95 bg-rose-500/10 text-rose-400 border border-rose-500/30 hover:bg-rose-500/20';
                iconMic.setAttribute('data-lucide', 'mic-off');
                textMic.innerText = 'Micro: Tắt';
                if (micBadgeIcon) micBadgeIcon.className = 'text-rose-400';
                if (micBadgeText) micBadgeText.innerText = 'Micro đang tắt';
            } else {
                btnMic.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all active:scale-95 bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20';
                iconMic.setAttribute('data-lucide', 'mic');
                textMic.innerText = 'Micro: Bật';
                if (micBadgeIcon) micBadgeIcon.className = 'text-emerald-400';
                if (micBadgeText) micBadgeText.innerText = 'Micro sẵn sàng';
            }
        }

        const btnCam = document.getElementById('btn-lobby-cam');
        const iconCam = document.getElementById('icon-lobby-cam');
        const textCam = document.getElementById('text-lobby-cam');

        if (btnCam && iconCam && textCam) {
            if (lobbyMutedVideo) {
                btnCam.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all active:scale-95 bg-rose-500/10 text-rose-400 border border-rose-500/30 hover:bg-rose-500/20';
                iconCam.setAttribute('data-lucide', 'video-off');
                textCam.innerText = 'Camera: Tắt';
            } else {
                btnCam.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all active:scale-95 bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 hover:bg-indigo-500/20';
                iconCam.setAttribute('data-lucide', 'video');
                textCam.innerText = 'Camera: Bật';
            }
        }

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    window.closeMeetingLobby = function() {
        if (lobbyPreviewStream) {
            lobbyPreviewStream.getTracks().forEach(t => t.stop());
            lobbyPreviewStream = null;
        }
        const modalLobby = document.getElementById('modal-meeting-lobby');
        if (modalLobby) {
            modalLobby.classList.add('hidden');
        }
    };

    window.confirmJoinFromLobby = function() {
        const options = {
            mutedAudio: lobbyMutedAudio,
            mutedVideo: lobbyMutedVideo
        };
        closeMeetingLobby();
        startCall(lobbyTargetCallType, options, 'classroom');
    };

    // --- Dieu khien tuy chon tien cuoc goi (Pre-call Controls) ---
    function updatePreCallUI() {
        const btnMic = document.getElementById('btn-precall-mic');
        const iconMic = document.getElementById('icon-precall-mic');
        const textMic = document.getElementById('text-precall-mic');

        if (btnMic && iconMic && textMic) {
            if (preCallMutedAudio) {
                btnMic.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition-all active:scale-95 hover:bg-rose-500/20';
                iconMic.setAttribute('data-lucide', 'mic-off');
                textMic.innerText = 'Mic: Tắt';
            } else {
                btnMic.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 transition-all active:scale-95 hover:bg-emerald-500/20';
                iconMic.setAttribute('data-lucide', 'mic');
                textMic.innerText = 'Mic: Bật';
            }
        }

        const btnCam = document.getElementById('btn-precall-cam');
        const iconCam = document.getElementById('icon-precall-cam');
        const textCam = document.getElementById('text-precall-cam');

        if (btnCam && iconCam && textCam) {
            if (activeCallType === 'voice') {
                btnCam.classList.add('hidden');
            } else {
                btnCam.classList.remove('hidden');
                if (preCallMutedVideo) {
                    btnCam.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition-all active:scale-95 hover:bg-rose-500/20';
                    iconCam.setAttribute('data-lucide', 'video-off');
                    textCam.innerText = 'Camera: Tắt';
                } else {
                    btnCam.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 transition-all active:scale-95 hover:bg-indigo-500/20';
                    iconCam.setAttribute('data-lucide', 'video');
                    textCam.innerText = 'Camera: Bật';
                }
            }
        }

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    window.togglePreCallMic = function() {
        preCallMutedAudio = !preCallMutedAudio;
        updatePreCallUI();
    };

    window.togglePreCallCam = function() {
        preCallMutedVideo = !preCallMutedVideo;
        updatePreCallUI();
    };

    // --- Xu ly khi co cuoc goi den ---
    function handleIncomingCall(data) {
        incomingCallData = data;
        activeRoomCode = data.roomCode;
        activeCallType = data.callType;

        if (incomingCallRingTimeoutTimer) {
            clearTimeout(incomingCallRingTimeoutTimer);
        }
        incomingCallRingTimeoutTimer = setTimeout(() => {
            stopRingtone();
            const modalIncoming = document.getElementById('modal-incoming-call');
            if (modalIncoming) modalIncoming.classList.add('hidden');
            if (incomingCallData) {
                if (typeof Toastify === 'function') {
                    Toastify({
                        text: 'Cuộc gọi đến đã kết thúc (không trả lời).',
                        style: { background: '#64748b', borderRadius: '0.5rem' },
                        duration: 3500
                    }).showToast();
                }
                incomingCallData = null;
            }
            incomingCallRingTimeoutTimer = null;
        }, 45000);

        preCallMutedAudio = false;
        preCallMutedVideo = false;

        const nameEl = document.getElementById('incoming-call-name');
        const avatarEl = document.getElementById('incoming-call-avatar');
        const badgeContainer = document.getElementById('incoming-call-badge-container');
        const badgeIcon = document.getElementById('incoming-call-badge-icon');
        const badgeText = document.getElementById('incoming-call-badge-text');
        const pulse1 = document.getElementById('incoming-call-pulse-1');
        const pulse2 = document.getElementById('incoming-call-pulse-2');

        if (nameEl) nameEl.innerText = data.senderName;

        if (data.callType === 'voice') {
            if (badgeContainer) badgeContainer.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800';
            if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'phone');
            if (badgeText) badgeText.innerText = 'Cuộc gọi thoại đến';
            if (pulse1) pulse1.className = 'absolute inset-0 rounded-full bg-emerald-500/20 animate-ping';
            if (pulse2) pulse2.className = 'absolute inset-1 rounded-full bg-emerald-500/30 animate-pulse';
        } else {
            if (badgeContainer) badgeContainer.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800';
            if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'video');
            if (badgeText) badgeText.innerText = 'Cuộc gọi video đến';
            if (pulse1) pulse1.className = 'absolute inset-0 rounded-full bg-indigo-500/20 animate-ping';
            if (pulse2) pulse2.className = 'absolute inset-1 rounded-full bg-indigo-500/30 animate-pulse';
        }

        if (avatarEl) {
            if (data.senderAvatar) {
                avatarEl.innerHTML = `<img src="${data.senderAvatar}" class="w-full h-full object-cover" alt="${data.senderName}">`;
            } else {
                avatarEl.innerText = data.senderName ? data.senderName.charAt(0).toUpperCase() : 'U';
            }
        }

        updatePreCallUI();

        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.remove('hidden');

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }

        startIncomingRingtone();
    }

    // --- Chap nhan cuoc goi den ---
    window.acceptIncomingCall = async function() {
        stopRingtone();
        if (incomingCallRingTimeoutTimer) {
            clearTimeout(incomingCallRingTimeoutTimer);
            incomingCallRingTimeoutTimer = null;
        }
        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.add('hidden');

        if (!incomingCallData) return;

        isInitiator = false;
        activeConversationId = incomingCallData.conversationId;
        activeRoomCode = incomingCallData.roomCode;
        activeCallType = incomingCallData.callType;
        ensureConversationEchoSubscribed(activeConversationId);

        try {
            const constraints = {
                audio: true,
                video: activeCallType === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
            };

            localStream = await navigator.mediaDevices.getUserMedia(constraints);

            isMutedAudio = preCallMutedAudio;
            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !isMutedAudio;
            }

            if (activeCallType === 'video') {
                isMutedVideo = preCallMutedVideo;
                const videoTrack = localStream.getVideoTracks()[0];
                if (videoTrack) {
                    videoTrack.enabled = !isMutedVideo;
                }
            } else {
                isMutedVideo = true;
            }

            openMeetingRoomModal();
            attachLocalMediaStream(localStream);
            updateMediaControlsUI();

            const statusBadge = document.getElementById('meeting-status-badge');
            if (statusBadge) {
                statusBadge.innerText = 'Đang diễn ra';
                statusBadge.className = 'text-emerald-400 font-medium';
            }

            isGroupMeeting = !!incomingCallData?.payload?.is_group;
            activeCallMode = incomingCallData?.payload?.mode || 'call';
            isMeetingHost = false;
            currentHostUserId = incomingCallData?.payload?.host_id || null;
            isHandRaised = false;
            updateMeetingRoleUI();
            updateAdaptiveVideoGrid();

            const meetingTitleEl = document.getElementById('meeting-room-title');
            if (meetingTitleEl) {
                if (isGroupMeeting) {
                    meetingTitleEl.innerText = incomingCallData?.payload?.title || 'Phòng học nhóm trực tuyến';
                } else {
                    meetingTitleEl.innerText = (activeCallType === 'voice') ? 'Cuộc gọi thoại' : 'Cuộc gọi video';
                }
            }

            startCallTimer();

            // Luu ho so nguoi goi
            const callerId = incomingCallData.senderId;
            if (callerId) {
                remoteUserProfiles[callerId] = {
                    name: incomingCallData.senderName,
                    avatar: incomingCallData.senderAvatar
                };
            }

            // Gui tin hieu accept_call sang phia nguoi goi de dung chuong cho doi
            await sendSignal('accept_call', { accepted: true }, callerId);

            // Tham gia phong tren backend va lay danh sach existing_participants
            const res = await fetch(`/app/conversation/${activeConversationId}/meeting/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ type: activeCallType, mode: activeCallMode })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                activeMeetingId = data.meeting?.id;
                activeRoomCode = data.room_code;
                if (data.existing_participants && Array.isArray(data.existing_participants)) {
                    data.existing_participants.forEach(p => {
                        initiateConnectionWithPeer(p.id, { name: p.name, avatar: p.avatar });
                    });
                } else if (callerId) {
                    initiateConnectionWithPeer(callerId, {
                        name: incomingCallData.senderName,
                        avatar: incomingCallData.senderAvatar
                    });
                }
            } else if (callerId) {
                initiateConnectionWithPeer(callerId, {
                    name: incomingCallData.senderName,
                    avatar: incomingCallData.senderAvatar
                });
            }

            // Thong bao ngay trang thai Mic/Cam ban dau sang cho cac thanh vien
            sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen });
        } catch (err) {
            console.error('Loi chap nhan cuoc goi:', err);
            if (typeof Toastify === 'function') {
                Toastify({ text: 'Khong the truy cap thiet bi Micro hoac Camera.', style: { background: '#f43f5e' } }).showToast();
            }
            rejectIncomingCall();
        }
    };

    // --- Tu choi cuoc goi den ---
    window.rejectIncomingCall = async function() {
        stopRingtone();
        if (incomingCallRingTimeoutTimer) {
            clearTimeout(incomingCallRingTimeoutTimer);
            incomingCallRingTimeoutTimer = null;
        }
        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.add('hidden');

        if (incomingCallData) {
            const convId = incomingCallData.conversationId || activeConversationId;
            try {
                await fetch(`/app/conversation/${convId}/meeting/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        room_code: incomingCallData.roomCode,
                        call_type: incomingCallData.callType
                    })
                });
            } catch (e) {
                console.error('Loi reject:', e);
            }
        }
        incomingCallData = null;
    };

    // -------------------------------------------------------------
    // WEBRTC FULL-MESH CORE ENGINES
    // -------------------------------------------------------------

    // Tao hoac lay PeerConnection tuong ung voi tung thanh vien remote
    function getOrCreatePeerConnection(remoteUserId) {
        const rId = Number(remoteUserId);
        if (peers[rId]) {
            return peers[rId];
        }

        const pc = new RTCPeerConnection(rtcConfig);
        peers[rId] = pc;
        pendingIceCandidates[rId] = pendingIceCandidates[rId] || [];

        // Nap track tu localStream vao ket noi
        if (localStream) {
            localStream.getTracks().forEach(track => {
                pc.addTrack(track, localStream);
            });
        }

        // Neu dang chia se man hinh, thay the video track bang screen track
        if (isSharingScreen && screenStream) {
            const screenTrack = screenStream.getVideoTracks()[0];
            if (screenTrack) {
                const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender) {
                    sender.replaceTrack(screenTrack);
                }
            }
        }

        // Lang nghe Remote Track tu thanh vien nay
        pc.ontrack = (event) => {
            let stream = remoteStreams[rId];
            if (!stream) {
                stream = (event.streams && event.streams[0]) ? event.streams[0] : new MediaStream();
                remoteStreams[rId] = stream;
            }

            if (event.streams && event.streams[0]) {
                remoteStreams[rId] = event.streams[0];
                stream = remoteStreams[rId];
            } else {
                if (!stream.getTracks().find(t => t.id === event.track.id)) {
                    stream.addTrack(event.track);
                }
            }

            if (event.track) {
                event.track.onunmute = () => {
                    renderOrUpdateParticipantCard(rId);
                    if (currentScreenSharerId && Number(currentScreenSharerId) === rId) {
                        const screenVideo = document.getElementById('screen-share-video');
                        if (screenVideo && screenVideo.paused) {
                            screenVideo.play().catch(e => console.warn('Spotlight onunmute play waiting:', e));
                        }
                    }
                };
            }

            renderOrUpdateParticipantCard(rId);

            // Neu thanh vien nay dang chia se man hinh, nap luong vao video spotlight
            if (currentScreenSharerId && Number(currentScreenSharerId) === rId) {
                const screenVideo = document.getElementById('screen-share-video');
                if (screenVideo) {
                    screenVideo.muted = true;
                    if (screenVideo.srcObject !== stream) {
                        screenVideo.srcObject = stream;
                    }
                    const playOnTrack = () => {
                        if (screenVideo.paused) {
                            screenVideo.play().catch(e => console.warn('Screen video play retry blocked:', e));
                        }
                    };
                    screenVideo.onloadedmetadata = playOnTrack;
                    playOnTrack();
                }
            }

            const statusBadge = document.getElementById('meeting-status-badge');
            if (statusBadge) {
                statusBadge.innerText = 'Đang diễn ra';
                statusBadge.className = 'text-emerald-400 font-medium';
            }
        };

        // Lang nghe ICE Candidate va gui chi dinh den remoteUserId
        pc.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal('webrtc_ice_candidate', { candidate: event.candidate }, rId);
            }
        };

        pc.onconnectionstatechange = () => {
            const state = pc.connectionState;
            if (state === 'connected') {
                if (peerDisconnectTimers[rId]) {
                    clearTimeout(peerDisconnectTimers[rId]);
                    delete peerDisconnectTimers[rId];
                }
                renderOrUpdateParticipantCard(rId);
                updateAdaptiveVideoGrid();
            } else if (state === 'disconnected') {
                console.warn(`Peer ${rId} connection disconnected, cho phuc hoi trong 8 giay...`);
                if (!peerDisconnectTimers[rId]) {
                    peerDisconnectTimers[rId] = setTimeout(() => {
                        console.warn(`Peer ${rId} mat ket noi qua lau, go khoi phong hop.`);
                        removeParticipant(rId);
                        delete peerDisconnectTimers[rId];
                    }, 8000);
                }
            } else if (state === 'failed' || state === 'closed') {
                console.warn(`Peer ${rId} connection state: ${state}`);
                if (peerDisconnectTimers[rId]) {
                    clearTimeout(peerDisconnectTimers[rId]);
                    delete peerDisconnectTimers[rId];
                }
                removeParticipant(rId);
            }
        };

        return pc;
    }

    // Khoi tao lien ket Mesh voi mot nguoi dung cu the
    function initiateConnectionWithPeer(remoteUserId, profile = null) {
        const rId = Number(remoteUserId);
        if (!rId || rId === Number(currentUserId)) return;

        if (profile) {
            remoteUserProfiles[rId] = {
                name: profile.name || 'Thành viên',
                avatar: profile.avatar || null
            };
        } else if (!remoteUserProfiles[rId]) {
            remoteUserProfiles[rId] = {
                name: 'Thành viên',
                avatar: null
            };
        }

        getOrCreatePeerConnection(rId);
        renderOrUpdateParticipantCard(rId);

        // Quy tac Tie-breaker chong xung dot Offer (Ke thua tu Nextcloud Talk):
        // Nguoi dung co ID lon hon dong vai tro Initiator tao Offer. ID nho hon cho Offer.
        const shouldInitiate = Number(currentUserId) > rId;
        if (shouldInitiate) {
            createAndSendOffer(rId);
        }
    }

    // Tao va gui Offer toi nguoi dung cu the (ho tro force tai dam phan khi them track)
    async function createAndSendOffer(remoteUserId, force = false) {
        const rId = Number(remoteUserId);
        const pc = getOrCreatePeerConnection(rId);
        if (!pc) return;

        // Tranh tao Offer trung lap neu ket noi dang trong qua trinh dam phan
        if (pc.signalingState !== 'stable') {
            console.warn(`Bo qua tao Offer cho peer ${rId} do signalingState dang la: ${pc.signalingState}`);
            return;
        }

        // Neu da connected va khong phai cuoc goi bat buoc (force / chia se man hinh) thi khong tao lai
        if (pc.connectionState === 'connected' && !isSharingScreen && !force) {
            return;
        }

        try {
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            await sendSignal('webrtc_offer', { sdp: pc.localDescription }, rId);
        } catch (err) {
            console.error(`Loi tao Offer cho peer ${rId}:`, err);
        }
    }

    // Xu ly nhan Offer tu nguoi dung khac
    async function handleReceiveOffer(senderId, sdp) {
        const sId = Number(senderId);
        const pc = getOrCreatePeerConnection(sId);

        try {
            // Perfect Negotiation pattern: Xu ly va cham glare khi ca 2 cung gui offer
            if (pc.signalingState !== 'stable') {
                if (Number(currentUserId) < sId) {
                    await pc.setLocalDescription({ type: 'rollback' });
                } else {
                    return;
                }
            }

            // Chuan hoa chuoi SDP: Dam bao ngat dong CRLF (\r\n) va dong cuoi luon co \r\n theo chuan RFC
            const rawOfferSdp = (typeof sdp === 'object' && sdp.sdp) ? sdp.sdp : sdp;
            const offerType = (typeof sdp === 'object' && sdp.type) ? sdp.type : 'offer';
            const formattedOfferSdp = String(rawOfferSdp).replace(/\r?\n/g, '\r\n').trimEnd() + '\r\n';

            try {
                await pc.setRemoteDescription(new RTCSessionDescription({
                    type: offerType,
                    sdp: formattedOfferSdp
                }));
                console.log(`[WebRTC] Set Remote Offer thanh cong tu peer ${sId}`);
            } catch (sdpErr) {
                console.warn(`[WebRTC] Nap Offer goc that bai (${sdpErr.message}), tien hanh loc bo dong ssrc msid loi theo chuan Unified Plan...`);
                // Loc bo cac dong a=ssrc:... msid:... loi/khong hop le
                const sanitizedOfferSdp = formattedOfferSdp
                    .split(/\r?\n/)
                    .filter(line => !line.match(/^a=ssrc:\d+\s+msid:/))
                    .join('\r\n')
                    .trimEnd() + '\r\n';

                await pc.setRemoteDescription(new RTCSessionDescription({
                    type: offerType,
                    sdp: sanitizedOfferSdp
                }));
                console.log(`[WebRTC] Set Remote Offer (Sanitized) thanh cong tu peer ${sId}!`);
            }
            await drainPendingIceCandidates(sId);

            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await sendSignal('webrtc_answer', { sdp: pc.localDescription }, sId);
        } catch (err) {
            console.error(`Loi xu ly Offer tu peer ${sId}:`, err);
        }
    }

    // Xu ly nhan Answer tu nguoi dung khac
    async function handleReceiveAnswer(senderId, sdp) {
        const sId = Number(senderId);
        const pc = peers[sId];
        if (!pc) return;

        try {
            // Chuan hoa chuoi SDP Answer: Dam bao ngat dong CRLF (\r\n) va dong cuoi luon co \r\n theo chuan RFC
            const rawAnswerSdp = (typeof sdp === 'object' && sdp.sdp) ? sdp.sdp : sdp;
            const answerType = (typeof sdp === 'object' && sdp.type) ? sdp.type : 'answer';
            const formattedAnswerSdp = String(rawAnswerSdp).replace(/\r?\n/g, '\r\n').trimEnd() + '\r\n';

            try {
                await pc.setRemoteDescription(new RTCSessionDescription({
                    type: answerType,
                    sdp: formattedAnswerSdp
                }));
                console.log(`[WebRTC] Set Remote Answer thanh cong tu peer ${sId}`);
            } catch (sdpErr) {
                console.warn(`[WebRTC] Nap Answer goc that bai (${sdpErr.message}), tien hanh loc bo dong ssrc msid loi theo chuan Unified Plan...`);
                const sanitizedAnswerSdp = formattedAnswerSdp
                    .split(/\r?\n/)
                    .filter(line => !line.match(/^a=ssrc:\d+\s+msid:/))
                    .join('\r\n')
                    .trimEnd() + '\r\n';

                await pc.setRemoteDescription(new RTCSessionDescription({
                    type: answerType,
                    sdp: sanitizedAnswerSdp
                }));
                console.log(`[WebRTC] Set Remote Answer (Sanitized) thanh cong tu peer ${sId}!`);
            }
            await drainPendingIceCandidates(sId);
        } catch (err) {
            console.error(`Loi xu ly Answer tu peer ${sId}:`, err);
        }
    }

    // Xu ly nhan ICE Candidate tu nguoi dung khac
    async function handleReceiveIceCandidate(senderId, candidate) {
        if (!candidate) return;
        const sId = Number(senderId);
        const pc = peers[sId];

        if (pc && pc.remoteDescription && pc.remoteDescription.type) {
            try {
                await pc.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (err) {
                console.warn(`Loi nap ICE Candidate cho peer ${sId}:`, err);
            }
        } else {
            pendingIceCandidates[sId] = pendingIceCandidates[sId] || [];
            pendingIceCandidates[sId].push(candidate);
        }
    }

    // Giai phong hang doi ICE Candidate khi RemoteDescription da san sang
    async function drainPendingIceCandidates(senderId) {
        const sId = Number(senderId);
        const pc = peers[sId];
        if (!pc || !pendingIceCandidates[sId]) return;

        while (pendingIceCandidates[sId].length > 0) {
            const candidate = pendingIceCandidates[sId].shift();
            try {
                await pc.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (err) {
                console.warn(`Loi giai phong ICE Candidate cho peer ${sId}:`, err);
            }
        }
    }

    // --- Gui tin hieu Signaling qua Reverb (Ho tro muc tieu target_user_id) ---
    async function sendSignal(action, payload = {}, targetUserId = null) {
        if (!activeConversationId) return;
        try {
            await fetch(`/app/conversation/${activeConversationId}/meeting/signal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    action: action,
                    room_code: activeRoomCode,
                    call_type: activeCallType,
                    target_user_id: targetUserId ? Number(targetUserId) : null,
                    payload: payload
                })
            });
        } catch (err) {
            console.error('Loi gui tin hieu signal:', err);
        }
    }

    // --- Dieu khien Micro ---
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

    // --- Dieu khien Camera & Nang cap linh hoat sang Video Call tren toan Mesh ---
    window.toggleCamera = async function() {
        if (!localStream) return;

        if (isSharingScreen) {
            if (typeof Toastify === 'function') {
                Toastify({ 
                    text: 'Bạn đang chia sẻ màn hình. Vui lòng tắt chia sẻ trước khi dùng Camera.', 
                    style: { background: '#f59e0b', borderRadius: '0.5rem' } 
                }).showToast();
            }
            return;
        }

        let videoTrack = localStream.getVideoTracks()[0];

        if (!videoTrack) {
            // Bat dau tu cuoc goi Thoai -> Nang cap len Video Call
            try {
                const newStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                videoTrack = newStream.getVideoTracks()[0];
                localStream.addTrack(videoTrack);
                activeCallType = 'video';
                isMutedVideo = false;

                // Nap track moi vao tat ca cac ket noi Mesh dang co
                for (const peerId in peers) {
                    const pc = peers[peerId];
                    const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                    if (sender) {
                        await sender.replaceTrack(videoTrack);
                    } else {
                        pc.addTrack(videoTrack, localStream);
                        await createAndSendOffer(peerId, true);
                    }
                }

                attachLocalMediaStream(localStream);
                updateMediaControlsUI();
                sendSignal('media_state_changed', { 
                    isMutedAudio, 
                    isMutedVideo: false, 
                    isSharingScreen, 
                    upgradedToVideo: true 
                });

                if (typeof Toastify === 'function') {
                    Toastify({ 
                        text: 'Đã bật Camera và chuyển sang cuộc gọi Video.', 
                        style: { background: '#0ea5e9', borderRadius: '0.5rem' } 
                    }).showToast();
                }
            } catch (err) {
                console.error('Loi xin quyen camera khi nang cap:', err);
                if (typeof Toastify === 'function') {
                    Toastify({ 
                        text: 'Không thể truy cập Camera của thiết bị.', 
                        style: { background: '#f43f5e', borderRadius: '0.5rem' } 
                    }).showToast();
                }
                return;
            }
        } else {
            // Da co video track -> Bat / Tat binh thuong
            isMutedVideo = !isMutedVideo;
            videoTrack.enabled = !isMutedVideo;
            updateMediaControlsUI();
            sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen });
        }
    };

    // --- Chia se man hinh Full-Mesh ---
    window.toggleScreenShare = async function() {
        if (isSharingScreen) {
            stopScreenShare();
        } else {
            await startScreenShare();
        }
    };

    async function startScreenShare() {
        if (currentScreenSharerId && Number(currentScreenSharerId) !== Number(currentUserId)) {
            const confirmMsg = `Thành viên "${currentScreenSharerName || 'đối phương'}" đang chia sẻ màn hình. Bạn có chắc muốn bắt đầu chia sẻ và thay thế màn hình đang chiếu không?`;
            if (!confirm(confirmMsg)) {
                return;
            }
        }

        try {
            const currentVideoTrack = localStream?.getVideoTracks()[0];
            wasCameraActiveBeforeScreenShare = !!(currentVideoTrack && currentVideoTrack.enabled && !isMutedVideo && activeCallType === 'video');

            screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: { cursor: 'always' },
                audio: {
                    echoCancellation: false,
                    autoGainControl: false,
                    noiseSuppression: false
                }
            });

            isSharingScreen = true;
            currentScreenSharerId = currentUserId;
            currentScreenSharerName = currentUserName;

            // Tam tat camera tren thiet bi cua ben share de ho thay ro camera da tat
            if (currentVideoTrack) {
                currentVideoTrack.enabled = false;
            }
            isMutedVideo = true;

            const screenTrack = screenStream.getVideoTracks()[0];
            if (screenTrack) {
                screenTrack.contentHint = 'detail';
                screenTrack.enabled = true;
            }

            // Thay the track tren tat ca cac Peer trong Mesh
            for (const peerId in peers) {
                const pc = peers[peerId];
                let sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (!sender) {
                    sender = pc.getSenders().find(s => s.track === null);
                }

                if (sender) {
                    await sender.replaceTrack(screenTrack);
                } else {
                    pc.addTrack(screenTrack, screenStream);
                    await createAndSendOffer(peerId, true);
                }
            }

            // Hien thi Spotlight trinh chieu tren UI cua chinh minh
            const spotlightContainer = document.getElementById('screen-share-spotlight');
            const screenVideo = document.getElementById('screen-share-video');
            const mirrorPlaceholder = document.getElementById('screen-mirror-placeholder');
            const screenSharerName = document.getElementById('screen-sharer-name');

            if (spotlightContainer) spotlightContainer.classList.remove('hidden');
            if (screenVideo) {
                screenVideo.muted = true;
                screenVideo.srcObject = screenStream;
                screenVideo.play().catch(e => console.warn('Screen video play blocked:', e));
            }
            if (screenSharerName) screenSharerName.innerText = 'Màn hình của bạn';

            if (mirrorPlaceholder) {
                mirrorPlaceholder.classList.add('hidden');
            }

            // Lang nghe khi nguoi dung bam Dung chia se tren thanh cong cu trinh duyet
            screenTrack.onended = () => {
                stopScreenShare();
            };

            updateMediaControlsUI();
            sendSignal('screen_share_started', { 
                sharerId: currentUserId, 
                sharerName: currentUserName 
            });

            if (typeof Toastify === 'function') {
                Toastify({
                    text: wasCameraActiveBeforeScreenShare 
                        ? 'Bạn đang chia sẻ màn hình (Camera của bạn đã tạm tắt).' 
                        : 'Bạn đang chia sẻ màn hình.',
                    style: { background: '#0284c7', borderRadius: '0.5rem' }
                }).showToast();
            }
        } catch (err) {
            console.error('Loi chia se man hinh:', err);
            isSharingScreen = false;
            // Neu user bam Huy chia se tren popup trinh duyet, khoi phuc lai camera neu truoc do co bat
            if (wasCameraActiveBeforeScreenShare) {
                const currentVideoTrack = localStream?.getVideoTracks()[0];
                if (currentVideoTrack) currentVideoTrack.enabled = true;
                isMutedVideo = false;
            }
            updateMediaControlsUI();
        }
    }

    window.stopScreenShare = function(notifySignal = true) {
        if (screenStream) {
            screenStream.getTracks().forEach(t => t.stop());
            screenStream = null;
        }

        isSharingScreen = false;
        if (currentScreenSharerId === currentUserId) {
            currentScreenSharerId = null;
            currentScreenSharerName = '';
        }

        // Khoi phuc camera cu tren tat ca cac Peer trong Mesh
        const videoTrack = localStream?.getVideoTracks()[0];
        const restoreTrack = (wasCameraActiveBeforeScreenShare && videoTrack) ? videoTrack : null;

        if (restoreTrack) {
            isMutedVideo = false;
            restoreTrack.enabled = true;
        } else {
            isMutedVideo = true;
        }

        for (const peerId in peers) {
            const pc = peers[peerId];
            const sender = pc.getSenders().find(s => (s.track && s.track.kind === 'video') || s.track === null);
            if (sender) {
                sender.replaceTrack(restoreTrack);
            }
        }

        // An Spotlight tren man hinh cua chinh minh
        const spotlightContainer = document.getElementById('screen-share-spotlight');
        const mirrorPlaceholder = document.getElementById('screen-mirror-placeholder');
        const screenVideo = document.getElementById('screen-share-video');
        if (spotlightContainer) spotlightContainer.classList.add('hidden');
        if (mirrorPlaceholder) mirrorPlaceholder.classList.add('hidden');
        if (screenVideo) screenVideo.srcObject = null;

        updateMediaControlsUI();
        if (notifySignal) {
            sendSignal('screen_share_stopped', { sharerId: currentUserId });
            sendSignal('media_state_changed', { 
                isMutedAudio, 
                isMutedVideo, 
                isSharingScreen: false 
            });
        }

        if (typeof Toastify === 'function') {
            Toastify({
                text: restoreTrack ? 'Đã dừng chia sẻ màn hình. Camera đã được bật lại.' : 'Đã dừng chia sẻ màn hình.',
                style: { background: '#0ea5e9', borderRadius: '0.5rem' }
            }).showToast();
        }
    };

    // --- Ket thuc cuoc goi / Roi phong ---
    window.endCurrentCall = async function(endForAll = false) {
        stopRingtone();
        stopCallTimer();

        if (activeRoomCode && activeConversationId) {
            try {
                await fetch(`/app/conversation/${activeConversationId}/meeting/leave`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        room_code: activeRoomCode,
                        call_type: activeCallType,
                        end_for_all: endForAll
                    })
                });
            } catch (err) {
                console.error('Loi leave call:', err);
            }
        }

        if (typeof Toastify === 'function') {
            const leaveMsg = endForAll 
                ? 'Bạn đã kết thúc phòng học cho tất cả thành viên.' 
                : 'Bạn đã rời khỏi cuộc gọi.';
            Toastify({
                text: leaveMsg,
                style: { background: '#64748b', borderRadius: '0.5rem' },
                duration: 3000
            }).showToast();
        }

        cleanupCall();
    };

    function cleanupCall() {
        stopRingtone();
        stopCallTimer();

        if (incomingCallRingTimeoutTimer) {
            clearTimeout(incomingCallRingTimeoutTimer);
            incomingCallRingTimeoutTimer = null;
        }

        for (const pId in peerDisconnectTimers) {
            clearTimeout(peerDisconnectTimers[pId]);
        }
        peerDisconnectTimers = {};

        if (localStream) {
            localStream.getTracks().forEach(t => t.stop());
            localStream = null;
        }

        if (screenStream) {
            screenStream.getTracks().forEach(t => t.stop());
            screenStream = null;
        }

        // Dong tat ca cac ket noi WebRTC Mesh
        for (const peerId in peers) {
            if (peers[peerId]) {
                peers[peerId].close();
            }
        }
        peers = {};
        remoteStreams = {};
        remoteUserProfiles = {};
        pendingIceCandidates = {};
        remoteUserStates = {};

        activeMeetingId = null;
        activeRoomCode = null;
        activeConversationId = pageConversationId;
        isSharingScreen = false;
        currentScreenSharerId = null;
        currentScreenSharerName = '';
        wasCameraActiveBeforeScreenShare = false;
        isInitiator = false;
        preCallMutedAudio = false;
        preCallMutedVideo = false;

        // Xoa tat ca the video remote khoi DOM
        const gridEl = document.getElementById('meeting-video-grid');
        if (gridEl) {
            const dynamicCards = gridEl.querySelectorAll('[id^="remote-card-"]');
            dynamicCards.forEach(c => c.remove());
        }

        teardownScreenShareSpotlight();
        updateAdaptiveVideoGrid();

        // An thanh Banner phong hoc nhom tren man hinh cua chinh minh
        const activeBanner = document.getElementById('active-meeting-banner');
        if (activeBanner) {
            activeBanner.classList.add('hidden');
            activeBanner.classList.remove('flex');
        }

        isGroupMeeting = false;
        isMeetingHost = false;
        currentHostUserId = null;
        isHandRaised = false;
        closeHostLeaveConfirmModal();
        updateMeetingRoleUI();

        const modalMeeting = document.getElementById('modal-meeting-room');
        if (modalMeeting) modalMeeting.classList.add('hidden');

        const modalIncoming = document.getElementById('modal-incoming-call');
        if (modalIncoming) modalIncoming.classList.add('hidden');
    }

    // --- Cap nhat giao dien phong hop ---
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
        const localAvatarImg = document.getElementById('local-avatar-img');
        const localAvatarLetter = document.getElementById('local-avatar-letter');

        if (currentUserAvatar && localAvatarImg) {
            localAvatarImg.src = currentUserAvatar;
            localAvatarImg.classList.remove('hidden');
            if (localAvatarLetter) localAvatarLetter.classList.add('hidden');
        } else if (localAvatarLetter) {
            localAvatarLetter.innerText = currentUserName.charAt(0).toUpperCase();
        }

        if (localVideo) {
            localVideo.srcObject = stream;
            if (activeCallType === 'voice' || isMutedVideo || isSharingScreen) {
                localVideo.classList.add('hidden');
                if (localFallback) localFallback.classList.remove('hidden');
            } else {
                localVideo.classList.remove('hidden');
                if (localFallback) localFallback.classList.add('hidden');
            }
        }
    }

    function updateMediaControlsUI() {
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

        const btnCam = document.getElementById('btn-call-cam');
        const iconCam = document.getElementById('icon-call-cam');
        const localVideo = document.getElementById('local-video');
        const localFallback = document.getElementById('local-video-fallback');
        const localFallbackText = document.querySelector('#local-video-fallback p');

        if (btnCam && iconCam) {
            if (isSharingScreen) {
                btnCam.disabled = true;
                btnCam.className = 'w-11 h-11 rounded-full bg-rose-600/70 text-white/80 opacity-70 cursor-not-allowed flex items-center justify-center transition-all';
                iconCam.setAttribute('data-lucide', 'video-off');
                btnCam.title = 'Camera tạm tắt khi chia sẻ màn hình';

                // Ben share cung thay ro rang camera cua minh bi tat:
                if (localVideo) localVideo.classList.add('hidden');
                if (localFallback) localFallback.classList.remove('hidden');
                if (localFallbackText) localFallbackText.innerText = 'Bạn (Camera tạm tắt do chia sẻ màn hình)';
            } else {
                btnCam.disabled = false;
                btnCam.removeAttribute('title');
                if (localFallbackText) localFallbackText.innerText = 'Bạn (Camera đang tắt)';

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
        }

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

    // Bo nho dem chong trung lap tin hieu signaling (Deduplication cache)
    const recentSignalSignatures = new Map();
    function isDuplicateSignal(e) {
        const payloadKey = (e.payload?.user_id || e.payload?.left_user_id || '') + '_' + (e.payload?.remaining_count ?? '');
        const key = `${e.action}_${e.senderId}_${e.roomCode || ''}_${payloadKey}`;
        const now = Date.now();
        const lastTime = recentSignalSignatures.get(key);
        if (lastTime && (now - lastTime) < 1500) {
            return true;
        }
        recentSignalSignatures.set(key, now);
        if (recentSignalSignatures.size > 50) {
            for (const [k, time] of recentSignalSignatures) {
                if (now - time > 5000) recentSignalSignatures.delete(k);
            }
        }
        return false;
    }

    // --- Ham xu ly tap trung cac su kien Signaling ---
    function handleCallSignal(e) {
        if (Number(e.senderId) === Number(currentUserId)) return;

        // Loc tin hieu huong dich: Neu tin hieu co chi dinh targetUserId ma khong phai minh thi bo qua
        const targetId = e.targetUserId || e.target_user_id;
        if (targetId && Number(targetId) !== Number(currentUserId)) {
            return;
        }

        // Bo loc chong trung lap cac su kien hien thi thong bao hoac cap nhat phong
        const toastActions = [
            'participant_joined', 'participant_left', 'end_call',
            'raise_hand', 'lower_hand', 'host_mute_user', 'host_mute_all',
            'meeting_started_banner'
        ];
        if (toastActions.includes(e.action) && isDuplicateSignal(e)) {
            return;
        }

        switch (e.action) {
            case 'meeting_started_banner':
                const activeBanner = document.getElementById('active-meeting-banner');
                const bannerHostDesc = document.getElementById('active-meeting-host-desc');
                if (activeBanner) {
                    activeBanner.classList.remove('hidden');
                    activeBanner.classList.add('flex');
                    if (bannerHostDesc) {
                        bannerHostDesc.innerText = `Chủ phòng: ${e.senderName || 'Bạn học'}`;
                    }
                    if (typeof lucide !== 'undefined' && lucide.createIcons) {
                        lucide.createIcons();
                    }
                }
                break;

            case 'incoming_call':
                handleIncomingCall(e);
                break;

            case 'accept_call':
                stopRingtone();
                startCallTimer();
                const statusBadge = document.getElementById('meeting-status-badge');
                if (statusBadge) {
                    statusBadge.innerText = 'Đang diễn ra';
                    statusBadge.className = 'text-emerald-400 font-medium';
                }

                const accepterId = e.senderId;
                if (accepterId) {
                    initiateConnectionWithPeer(accepterId, {
                        name: e.senderName,
                        avatar: e.senderAvatar
                    });
                }

                // Dong bo nguoi vao sau neu ban than dang chia se man hinh
                if (isSharingScreen && currentScreenSharerId === currentUserId && accepterId) {
                    sendSignal('screen_share_started', {
                        sharerId: currentUserId,
                        sharerName: currentUserName
                    }, accepterId);
                }
                break;

            case 'reject_call':
                const isGroupReject = !!(e.payload?.is_group || isGroupMeeting);
                if (isGroupReject) {
                    const rejectedName = e.payload?.rejected_user_name || e.senderName || 'Một thành viên';
                    if (typeof Toastify === 'function') {
                        Toastify({
                            text: `${rejectedName} đã từ chối tham gia cuộc gọi.`,
                            style: { background: '#f59e0b', borderRadius: '0.5rem' },
                            duration: 3500
                        }).showToast();
                    }
                    break;
                }

                stopRingtone();
                if (typeof Toastify === 'function') {
                    Toastify({ text: 'Đối phương đã từ chối cuộc gọi.', style: { background: '#f43f5e' } }).showToast();
                }
                cleanupCall();
                break;

            case 'participant_joined':
                const newUserId = e.payload?.user_id;
                if (newUserId && Number(newUserId) !== Number(currentUserId)) {
                    if (typeof Toastify === 'function') {
                        Toastify({
                            text: `${e.payload?.user_name || 'Một thành viên'} đã tham gia phòng học.`,
                            style: { background: '#0284c7', borderRadius: '0.5rem' },
                            duration: 3000
                        }).showToast();
                    }

                    initiateConnectionWithPeer(newUserId, {
                        name: e.payload?.user_name,
                        avatar: e.payload?.user_avatar
                    });

                    // Dong bo nguoi vao sau neu ban than dang chia se man hinh
                    if (isSharingScreen && currentScreenSharerId === currentUserId) {
                        sendSignal('screen_share_started', {
                            sharerId: currentUserId,
                            sharerName: currentUserName
                        }, newUserId);
                    }
                }
                break;

            case 'participant_left':
                const leftName = e.payload?.left_user_name || 'Một thành viên';
                const leftUserId = e.payload?.left_user_id;

                if (typeof Toastify === 'function') {
                    Toastify({
                        text: `${leftName} đã rời phòng học.`,
                        style: { background: '#64748b', borderRadius: '0.5rem' },
                        duration: 3000
                    }).showToast();
                }

                if (leftUserId) {
                    removeParticipant(leftUserId);
                }

                if (e.payload?.new_host_id && Number(e.payload.new_host_id) === Number(currentUserId)) {
                    isMeetingHost = true;
                    currentHostUserId = currentUserId;
                    updateMeetingRoleUI();
                    if (typeof Toastify === 'function') {
                        Toastify({
                            text: 'Bạn đã trở thành Chủ phòng mới.',
                            style: { background: '#059669', borderRadius: '0.5rem' },
                            duration: 4000
                        }).showToast();
                    }
                } else if (e.payload?.new_host_id) {
                    currentHostUserId = e.payload.new_host_id;
                    updateMeetingRoleUI();
                }
                break;

            case 'raise_hand':
                const handUserId = Number(e.payload?.userId || e.senderId);
                remoteUserStates[handUserId] = remoteUserStates[handUserId] || {};
                remoteUserStates[handUserId].isHandRaised = true;
                renderOrUpdateParticipantCard(handUserId);
                playTone(600, 800, 0.2);
                if (typeof Toastify === 'function') {
                    Toastify({
                        text: `${e.payload?.userName || e.senderName || 'Một thành viên'} đang giơ tay phát biểu.`,
                        style: { background: '#f59e0b', borderRadius: '0.5rem' },
                        duration: 3500
                    }).showToast();
                }
                break;

            case 'lower_hand':
                const lowerUserId = Number(e.payload?.userId || e.senderId);
                if (remoteUserStates[lowerUserId]) {
                    remoteUserStates[lowerUserId].isHandRaised = false;
                }
                renderOrUpdateParticipantCard(lowerUserId);
                break;

            case 'host_mute_user':
                if (localStream) {
                    const audioTrack = localStream.getAudioTracks()[0];
                    if (audioTrack && audioTrack.enabled) {
                        isMutedAudio = true;
                        audioTrack.enabled = false;
                        updateMediaControlsUI();
                        sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen });
                        if (typeof Toastify === 'function') {
                            Toastify({
                                text: 'Chủ phòng đã tắt micro của bạn.',
                                style: { background: '#f43f5e', borderRadius: '0.5rem' },
                                duration: 3500
                            }).showToast();
                        }
                    }
                }
                break;

            case 'host_mute_all':
                if (!isMeetingHost && localStream) {
                    const audioTrack = localStream.getAudioTracks()[0];
                    if (audioTrack && audioTrack.enabled) {
                        isMutedAudio = true;
                        audioTrack.enabled = false;
                        updateMediaControlsUI();
                        sendSignal('media_state_changed', { isMutedAudio, isMutedVideo, isSharingScreen });
                        if (typeof Toastify === 'function') {
                            Toastify({
                                text: 'Chủ phòng đã tắt micro tất cả thành viên.',
                                style: { background: '#f43f5e', borderRadius: '0.5rem' },
                                duration: 3500
                            }).showToast();
                        }
                    }
                }
                break;

            case 'webrtc_offer':
                handleReceiveOffer(e.senderId, e.payload.sdp);
                break;

            case 'webrtc_answer':
                handleReceiveAnswer(e.senderId, e.payload.sdp);
                break;

            case 'webrtc_ice_candidate':
                handleReceiveIceCandidate(e.senderId, e.payload.candidate);
                break;

            case 'media_state_changed':
                const sId = Number(e.senderId);
                remoteUserStates[sId] = e.payload;

                if (e.payload.upgradedToVideo) {
                    activeCallType = 'video';
                    if (typeof Toastify === 'function') {
                        Toastify({ 
                            text: `${e.senderName || 'Một thành viên'} đã bật Camera.`, 
                            style: { background: '#0ea5e9', borderRadius: '0.5rem' } 
                        }).showToast();
                    }
                }

                renderOrUpdateParticipantCard(sId);
                break;

            case 'screen_share_started':
                const newSharerId = Number(e.payload?.sharerId);
                const newSharerName = e.payload?.sharerName || e.senderName || 'Thành viên';

                if (isSharingScreen && newSharerId && newSharerId !== Number(currentUserId)) {
                    stopScreenShare(false);
                    if (typeof Toastify === 'function') {
                        Toastify({
                            text: `Thành viên "${newSharerName}" đã bắt đầu chia sẻ màn hình. Chia sẻ của bạn đã tạm dừng.`,
                            style: { background: '#f59e0b', borderRadius: '0.5rem' },
                            duration: 4000
                        }).showToast();
                    }
                }

                currentScreenSharerId = newSharerId;
                currentScreenSharerName = newSharerName;

                const spotlightContainer = document.getElementById('screen-share-spotlight');
                const screenVideo = document.getElementById('screen-share-video');
                const mirrorPlaceholder = document.getElementById('screen-mirror-placeholder');
                const screenSharerNameEl = document.getElementById('screen-sharer-name');

                if (spotlightContainer) spotlightContainer.classList.remove('hidden');
                if (mirrorPlaceholder) mirrorPlaceholder.classList.add('hidden');

                // Cap nhat the camera cua nguoi chia se (chuyen sang fallback de tranh suspension)
                renderOrUpdateParticipantCard(newSharerId);

                const sharerStream = remoteStreams[newSharerId];
                if (screenVideo && sharerStream) {
                    screenVideo.muted = true;
                    if (screenVideo.srcObject !== sharerStream) {
                        screenVideo.srcObject = sharerStream;
                    }

                    const playSpotlight = () => {
                        if (screenVideo.paused) {
                            screenVideo.play().catch(e => console.warn('Spotlight play waiting:', e));
                        }
                    };

                    screenVideo.onloadedmetadata = playSpotlight;
                    screenVideo.oncanplay = playSpotlight;

                    const vTrack = sharerStream.getVideoTracks ? sharerStream.getVideoTracks()[0] : null;
                    if (vTrack) {
                        vTrack.onunmute = playSpotlight;
                    }

                    playSpotlight();
                }

                if (screenSharerNameEl) {
                    screenSharerNameEl.innerText = `${newSharerName} đang trình chiếu`;
                }

                updateMediaControlsUI();
                break;

            case 'screen_share_stopped':
                const stoppedSharerId = Number(e.payload?.sharerId);
                if (!stoppedSharerId || stoppedSharerId === Number(currentScreenSharerId)) {
                    teardownScreenShareSpotlight();
                }
                break;

            case 'end_call':
                const bannerEnd = document.getElementById('active-meeting-banner');
                if (bannerEnd) {
                    bannerEnd.classList.add('hidden');
                    bannerEnd.classList.remove('flex');
                }
                if (typeof Toastify === 'function') {
                    Toastify({ text: 'Cuộc gọi / phòng họp đã kết thúc.', style: { background: '#64748b' } }).showToast();
                }
                cleanupCall();
                break;
        }
    }

    // --- Phong to rieng khung trinh chieu man hinh ---
    window.toggleScreenShareFullscreen = function() {
        const spot = document.getElementById('screen-share-spotlight');
        if (!spot) return;
        if (!document.fullscreenElement) {
            if (spot.requestFullscreen) {
                spot.requestFullscreen();
            } else if (spot.webkitRequestFullscreen) {
                spot.webkitRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
    };

    // --- Lang nghe su kien qua Laravel Reverb ---
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.Echo === 'undefined') return;

        // 1. Kenh ca nhan toan cuc cua nguoi dung: Nhan cuoc goi den moi luc
        window.Echo.private(`App.Models.User.${currentUserId}`)
            .listen('.CallSignalEvent', handleCallSignal);

        // 2. Kenh hoi thoai (neu dang o trong phong chat)
        if (activeConversationId) {
            ensureConversationEchoSubscribed(activeConversationId);
        }
    });

    // --- Tu dong roi phong khi tat tab hoac dong trinh duyet (Chong treo phong hop) ---
    window.addEventListener('beforeunload', () => {
        if (activeRoomCode && activeConversationId) {
            const leaveUrl = `/app/conversation/${activeConversationId}/meeting/leave`;
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('room_code', activeRoomCode);
                formData.append('call_type', activeCallType);
                formData.append('end_for_all', '0');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    formData.append('_token', csrfToken);
                }
                navigator.sendBeacon(leaveUrl, formData);
            }
        }
    });
})();
</script>
