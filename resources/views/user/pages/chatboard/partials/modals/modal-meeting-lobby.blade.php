<!-- MODAL: PHONG CHO CHUAN BI THAM GIA PHONG HOC NHOM (MEETING LOBBY) -->
<div id="modal-meeting-lobby" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center hidden p-4 select-none transition-all duration-300">
    <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-5 text-white">
        <!-- Header Phong cho -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                    <i data-lucide="video" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="lobby-title" class="font-bold text-sm text-white">
                        Chuẩn bị vào phòng học nhóm
                    </h3>
                    <p id="lobby-subtitle" class="text-xs text-slate-400">
                        Kiểm tra góc máy và âm thanh trước khi tham gia
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeMeetingLobby()" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition-colors" title="Đóng">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Khung xem truoc Camera (Gương soi Preview) -->
        <div class="relative w-full aspect-video bg-black rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-inner">
            <!-- Video preview cuc bo -->
            <video id="lobby-preview-video" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>

            <!-- Fallback khi tat camera -->
            <div id="lobby-camera-fallback" class="hidden absolute inset-0 bg-slate-900 flex flex-col items-center justify-center gap-3">
                <div class="w-20 h-20 rounded-full bg-slate-800 border-2 border-slate-700 text-slate-300 flex items-center justify-center font-extrabold text-2xl shadow-inner">
                    <span id="lobby-avatar-letter">U</span>
                </div>
                <p class="text-xs font-semibold text-slate-400">Camera đang tắt</p>
            </div>

            <!-- Indicator trang thai Micro o goc duoi -->
            <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md px-3 py-1 rounded-xl border border-white/10 flex items-center gap-2 text-xs">
                <span id="lobby-mic-badge-icon" class="text-emerald-400">
                    <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                </span>
                <span id="lobby-mic-badge-text" class="text-[11px] font-semibold text-slate-200">Micro sẵn sàng</span>
            </div>
        </div>

        <!-- Thanh dieu khien Micro va Camera truoc khi vao phong -->
        <div class="flex items-center justify-center gap-3">
            <!-- Bat / Tat Micro -->
            <button type="button" id="btn-lobby-mic" onclick="toggleLobbyMic()" 
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all active:scale-95 bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20">
                <i id="icon-lobby-mic" data-lucide="mic" class="w-4 h-4"></i>
                <span id="text-lobby-mic">Micro: Bật</span>
            </button>

            <!-- Bat / Tat Camera -->
            <button type="button" id="btn-lobby-cam" onclick="toggleLobbyCam()" 
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all active:scale-95 bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 hover:bg-indigo-500/20">
                <i id="icon-lobby-cam" data-lucide="video" class="w-4 h-4"></i>
                <span id="text-lobby-cam">Camera: Bật</span>
            </button>
        </div>

        <!-- Nut hanh dong: Tham gia / Huy -->
        <div class="space-y-2 pt-1">
            <button type="button" id="btn-lobby-confirm" onclick="confirmJoinFromLobby()" 
                    class="w-full py-3 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 active:scale-95">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span id="btn-lobby-confirm-text">Tham gia phòng học ngay</span>
            </button>

            <button type="button" onclick="closeMeetingLobby()" 
                    class="w-full py-2 px-4 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                Hủy bỏ
            </button>
        </div>
    </div>
</div>
