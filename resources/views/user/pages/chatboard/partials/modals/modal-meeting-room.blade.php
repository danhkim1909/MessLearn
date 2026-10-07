<!-- MODAL: PHONG HOP TRUC TUYEN & CHIA SE MAN HINH -->
<div id="modal-meeting-room" class="fixed inset-0 z-50 bg-slate-950 flex flex-col hidden select-none transition-all duration-300">
    <!-- Header phong hop -->
    <div class="h-14 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold">
                <i data-lucide="video" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 id="meeting-room-title" class="font-bold text-sm text-white truncate max-w-xs">
                    Phong hoc truc tuyen
                </h3>
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="meeting-timer">00:00</span>
                    <span>&bull;</span>
                    <span id="meeting-status-badge" class="text-emerald-400 font-medium">Dang ket noi</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleMeetingFullscreen()" 
                    class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" 
                    title="Toan man hinh">
                <i data-lucide="maximize-2" id="icon-fullscreen" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Khu vuc hien thi Video & Chia se man hinh -->
    <div id="meeting-main-content" class="flex-1 flex flex-col p-4 min-h-0 relative overflow-hidden">
        <!-- 1. Spotlight: Khung chia se man hinh (Ke thua tu Nextcloud Talk) -->
        <div id="screen-share-spotlight" class="hidden flex-1 relative bg-black rounded-2xl overflow-hidden border border-slate-800 mb-3 shadow-2xl">
            <video id="screen-share-video" autoplay playsinline class="w-full h-full object-contain"></video>
            
            <!-- Placeholder chong hieu ung guong vo tan khi chia se chinh tab nay -->
            <div id="screen-mirror-placeholder" class="hidden absolute inset-0 bg-slate-900/95 flex flex-col items-center justify-center p-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <i data-lucide="monitor-up" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-white">Ban dang chia se man hinh may tinh</h4>
                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                        Cac thanh vien khac trong phong dang theo doi man hinh trinh chieu cua ban.
                    </p>
                </div>
                <button type="button" onclick="stopScreenShare()" 
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-full font-bold text-xs shadow-lg shadow-rose-600/30 transition-transform active:scale-95 flex items-center gap-2">
                    <i data-lucide="square" class="w-4 h-4"></i>
                    <span>Dung chia se man hinh</span>
                </button>
            </div>

            <!-- Nhan nguoi dang chia se -->
            <div class="absolute top-3 left-3 bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-1.5">
                <i data-lucide="monitor" class="w-3.5 h-3.5 text-sky-400"></i>
                <span id="screen-sharer-name">Man hinh trinh chieu</span>
            </div>
        </div>

        <!-- 2. Luoi Camera thanh vien (Adaptive Video Grid) -->
        <div id="meeting-video-grid" class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3 min-h-0">
            <!-- Thẻ Camera doi phuong (Remote Peer) -->
            <div id="remote-video-card" class="relative bg-slate-900/90 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-lg transition-all duration-300">
                <video id="remote-video" autoplay playsinline class="w-full h-full object-cover"></video>
                
                <!-- Avatar thay the khi tat Camera -->
                <div id="remote-video-fallback" class="hidden flex flex-col items-center gap-3">
                    <div id="remote-avatar-letter" class="w-24 h-24 rounded-full bg-slate-800 border-2 border-slate-700 text-slate-300 flex items-center justify-center font-extrabold text-3xl shadow-inner">
                        U
                    </div>
                    <p id="remote-fallback-name" class="font-bold text-xs text-slate-300">Nguoi dung</p>
                </div>

                <!-- Thong tin va trang thai Mic cua doi phuong -->
                <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md text-white text-xs font-semibold px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-2">
                    <span id="remote-card-name">Ban be</span>
                    <span id="remote-mic-badge" class="text-emerald-400">
                        <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
            </div>

            <!-- Thẻ Camera cua ban (Local Peer) -->
            <div id="local-video-card" class="relative bg-slate-900/90 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-lg transition-all duration-300">
                <video id="local-video" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>
                
                <!-- Avatar thay the khi tat Camera -->
                <div id="local-video-fallback" class="hidden flex flex-col items-center gap-3">
                    <div id="local-avatar-letter" class="w-24 h-24 rounded-full bg-sky-900/60 border-2 border-sky-800 text-sky-200 flex items-center justify-center font-extrabold text-3xl shadow-inner">
                        B
                    </div>
                    <p class="font-bold text-xs text-slate-300">Ban (Camera dang tat)</p>
                </div>

                <!-- Thong tin va trang thai Mic cua ban -->
                <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md text-white text-xs font-semibold px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-2">
                    <span>Ban</span>
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
                    title="Bat / Tat Micro">
                <i data-lucide="mic" id="icon-call-mic" class="w-5 h-5"></i>
            </button>

            <!-- Bat / Tat Camera -->
            <button type="button" id="btn-call-cam" onclick="toggleCamera()" 
                    class="w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Bat / Tat Camera">
                <i data-lucide="video" id="icon-call-cam" class="w-5 h-5"></i>
            </button>

            <!-- Chia se man hinh -->
            <button type="button" id="btn-call-screen" onclick="toggleScreenShare()" 
                    class="w-11 h-11 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-all active:scale-95" 
                    title="Chia se man hinh">
                <i data-lucide="monitor-up" id="icon-call-screen" class="w-5 h-5"></i>
            </button>

            <div class="w-px h-6 bg-slate-800"></div>

            <!-- Ket thuc cuoc goi / Roi phong -->
            <button type="button" id="btn-call-end" onclick="endCurrentCall()" 
                    class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-600/30 flex items-center gap-2 transition-transform active:scale-95" 
                    title="Ket thuc cuoc goi">
                <i data-lucide="phone-off" class="w-4 h-4"></i>
                <span>Ket thuc</span>
            </button>
        </div>
    </div>
</div>
