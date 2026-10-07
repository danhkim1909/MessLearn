<!-- MODAL: PHONG HOP TRUC TUYEN & CHIA SE MAN HINH -->
<div id="modal-meeting-room" class="fixed inset-0 z-50 bg-slate-950 flex flex-col hidden select-none transition-all duration-300">
    <!-- The phat am thanh doc lap cho nguoi noi doi phuong -->
    <audio id="remote-audio" autoplay playsinline class="hidden"></audio>

    <!-- Header phong hop -->
    <div class="h-14 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold">
                <i data-lucide="video" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 id="meeting-room-title" class="font-bold text-sm text-white truncate max-w-xs">
                    Phòng họp trực tuyến
                </h3>
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="meeting-timer">00:00</span>
                    <span>&bull;</span>
                    <span id="meeting-status-badge" class="text-emerald-400 font-medium">Đang kết nối</span>
                    <span id="meeting-user-role-badge" class="hidden ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 items-center gap-1">
                        <i data-lucide="crown" class="w-3 h-3 text-amber-400"></i>
                        <span id="meeting-role-text">Chủ phòng</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleMeetingFullscreen()" 
                    class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" 
                    title="Toàn màn hình">
                <i data-lucide="maximize-2" id="icon-fullscreen" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Khu vuc hien thi Video & Chia se man hinh -->
    <div id="meeting-main-content" class="flex-1 flex flex-col p-4 min-h-0 relative overflow-hidden">
        <!-- 1. Spotlight: Khung chia se man hinh (Ke thua tu Nextcloud Talk) -->
        <div id="screen-share-spotlight" class="hidden flex-1 relative bg-black rounded-2xl overflow-hidden border border-slate-800 mb-3 shadow-2xl">
            <video id="screen-share-video" autoplay playsinline muted class="w-full h-full object-contain"></video>
            
            <!-- Placeholder chong hieu ung guong vo tan khi chia se chinh tab nay -->
            <div id="screen-mirror-placeholder" class="hidden absolute inset-0 bg-slate-900/95 flex flex-col items-center justify-center p-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <i data-lucide="monitor-up" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-white">Bạn đang chia sẻ màn hình máy tính</h4>
                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                        Các thành viên khác trong phòng đang theo dõi màn hình trình chiếu của bạn.
                    </p>
                </div>
                <button type="button" onclick="stopScreenShare()" 
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-full font-bold text-xs shadow-lg shadow-rose-600/30 transition-transform active:scale-95 flex items-center gap-2">
                    <i data-lucide="square" class="w-4 h-4"></i>
                    <span>Dừng chia sẻ màn hình</span>
                </button>
            </div>

            <!-- Nhan nguoi dang chia se -->
            <div class="absolute top-3 left-3 bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-1.5 z-10">
                <i data-lucide="monitor" class="w-3.5 h-3.5 text-sky-400"></i>
                <span id="screen-sharer-name">Màn hình trình chiếu</span>
            </div>

            <!-- Nut phong to rieng khung trinh chieu -->
            <button type="button" onclick="toggleScreenShareFullscreen()" class="absolute top-3 right-3 bg-black/60 hover:bg-black/80 backdrop-blur-md text-white/80 hover:text-white p-2 rounded-xl border border-white/10 transition-colors z-10" title="Toàn màn hình bài trình chiếu">
                <i data-lucide="maximize" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- 2. Luoi Camera thanh vien (Adaptive Video Grid) -->
        <div id="meeting-video-grid" class="flex-1 grid grid-cols-1 gap-3 min-h-0 relative">

            <!-- Thẻ Camera cua ban (Local Peer) -->
            <div id="local-video-card" class="relative bg-slate-900/90 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-lg transition-all duration-300">
                <video id="local-video" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>
                
                <!-- Badge thong bao dang cho thanh vien khac khi chi co 1 minh -->
                <div id="waiting-peer-badge" class="hidden absolute top-4 left-1/2 -translate-x-1/2 bg-slate-900/85 backdrop-blur-md text-slate-300 text-xs px-4 py-2 rounded-full border border-slate-700/60 shadow-lg items-center gap-2 pointer-events-none transition-all duration-300 z-10">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span>Đang chờ thành viên khác tham gia...</span>
                </div>

                <!-- Avatar thay the khi tat Camera -->
                <div id="local-video-fallback" class="hidden flex flex-col items-center gap-3">
                    <div id="local-avatar-container" class="w-24 h-24 rounded-full bg-sky-900/60 border-2 border-sky-800 text-sky-200 flex items-center justify-center font-extrabold text-3xl shadow-inner overflow-hidden">
                        <img id="local-avatar-img" class="hidden w-full h-full object-cover" alt="Local Avatar">
                        <span id="local-avatar-letter">B</span>
                    </div>
                    <p class="font-bold text-xs text-slate-300">Bạn (Camera đang tắt)</p>
                </div>

                <!-- Thong tin va trang thai Mic cua ban -->
                <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md text-white text-xs font-semibold px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-2">
                    <span id="local-crown-badge" class="hidden text-amber-400" title="Bạn là Chủ phòng"><i data-lucide="crown" class="w-3.5 h-3.5"></i></span>
                    <span>Bạn</span>
                    <span id="local-hand-badge" class="hidden text-amber-400 animate-bounce" title="Bạn đang giơ tay phát biểu"><i data-lucide="hand" class="w-3.5 h-3.5 fill-amber-400/30"></i></span>
                    <span id="local-mic-badge" class="text-emerald-400">
                        <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh cong cu dieu khien lo lung day phong (Floating Pill Control Bar) -->
    <div class="h-20 flex items-center justify-center shrink-0 z-30 pb-2">
        <div id="meeting-controls-bar" class="bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-full px-6 py-3 flex items-center gap-4 shadow-2xl">
            <!-- Bat / Tat Micro -->
            <button type="button" id="btn-call-mic" onclick="toggleMicrophone()" 
                    class="w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Bật / Tắt Micro">
                <i data-lucide="mic" id="icon-call-mic" class="w-5 h-5"></i>
            </button>

            <!-- Bat / Tat Camera -->
            <button type="button" id="btn-call-cam" onclick="toggleCamera()" 
                    class="w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Bật / Tắt Camera">
                <i data-lucide="video" id="icon-call-cam" class="w-5 h-5"></i>
            </button>

            <!-- Chia se man hinh -->
            <button type="button" id="btn-call-screen" onclick="toggleScreenShare()" 
                    class="w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Chia sẻ màn hình">
                <i data-lucide="monitor-up" id="icon-call-screen" class="w-5 h-5"></i>
            </button>

            <!-- Gio tay phat bieu (Danh cho phong hop nhom) -->
            <button type="button" id="btn-call-hand" onclick="toggleRaiseHand()" 
                    class="hidden w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Giơ tay phát biểu">
                <i data-lucide="hand" id="icon-call-hand" class="w-5 h-5"></i>
            </button>

            <!-- Tat mic ca phong (Danh rieng cho Chu phong Host trong phong hop nhom) -->
            <button type="button" id="btn-host-mute-all" onclick="hostMuteAllParticipants()" 
                    class="hidden w-11 h-11 rounded-full bg-slate-800 hover:bg-rose-600 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Chủ phòng: Tắt micro tất cả thành viên">
                <i data-lucide="volume-x" id="icon-host-mute-all" class="w-5 h-5"></i>
            </button>

            <div class="w-px h-6 bg-slate-800"></div>

            <!-- Ket thuc cuoc goi / Roi phong -->
            <button type="button" id="btn-call-end" onclick="handleCallEndButtonClick()" 
                    class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-600/30 flex items-center gap-2 transition-transform active:scale-95" 
                    title="Kết thúc / Rời phòng">
                <i data-lucide="phone-off" class="w-4 h-4"></i>
                <span id="btn-call-end-text">Kết thúc</span>
            </button>
        </div>
    </div>

    <!-- MODAL: XAC NHAN ROI PHONG CUA CHU PHONG (HOST LEAVE CONFIRMATION) -->
    <div id="modal-host-leave-confirm" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4" style="z-index: 100;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mx-auto">
                <i data-lucide="crown" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-base">Bạn là Chủ phòng học</h4>
                <p class="text-xs text-slate-400 mt-1">
                    Bạn muốn chỉ một mình bạn rời phòng hay kết thúc phòng học cho tất cả thành viên?
                </p>
            </div>
            <div class="space-y-2 pt-1">
                <button type="button" onclick="confirmHostLeave(false)" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Chỉ mình tôi rời phòng</span>
                </button>
                <button type="button" onclick="confirmHostLeave(true)" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 shadow-lg shadow-rose-600/30">
                    <i data-lucide="power" class="w-4 h-4"></i>
                    <span>Kết thúc phòng học cho tất cả</span>
                </button>
                <button type="button" onclick="closeHostLeaveConfirmModal()" class="w-full py-2 px-4 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    Hủy bỏ
                </button>
            </div>
        </div>
    </div>
</div>
