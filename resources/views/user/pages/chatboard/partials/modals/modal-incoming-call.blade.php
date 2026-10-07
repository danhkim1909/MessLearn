<!-- MODAL: CUOC GOI DEN -->
<div id="modal-incoming-call" class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-sm rounded-3xl p-6 shadow-2xl text-center space-y-5">
        <!-- Avatar nguoi goi kem hieu ung song am -->
        <div class="relative w-24 h-24 mx-auto mt-2">
            <div id="incoming-call-pulse-1" class="absolute inset-0 rounded-full bg-sky-500/20 animate-ping"></div>
            <div id="incoming-call-pulse-2" class="absolute inset-1 rounded-full bg-sky-500/30 animate-pulse"></div>
            <div id="incoming-call-avatar" class="relative w-24 h-24 rounded-full bg-sky-100 dark:bg-sky-900/60 text-sky-600 dark:text-sky-300 flex items-center justify-center font-extrabold text-2xl shadow-inner border-2 border-white dark:border-slate-800 overflow-hidden">
                U
            </div>
        </div>

        <!-- Thong tin cuoc goi & Badge nhan dien ro rang -->
        <div class="space-y-1.5">
            <div id="incoming-call-badge-container" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                <i id="incoming-call-badge-icon" data-lucide="video" class="w-3.5 h-3.5"></i>
                <span id="incoming-call-badge-text">Cuộc gọi video đến</span>
            </div>
            <h3 id="incoming-call-name" class="font-bold text-lg text-slate-900 dark:text-white truncate">
                Tên người gọi
            </h3>
            <p id="incoming-call-type-label" class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Đang đổ chuông...
            </p>
        </div>

        <!-- Pre-call Controls: Chon Bat/Tat Mic va Cam truoc khi nhan -->
        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-700/60 space-y-2">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Tùy chọn trước khi nhấc máy:</p>
            <div class="flex items-center justify-center gap-2">
                <!-- Toggle Mic truoc khi vao -->
                <button type="button" id="btn-precall-mic" onclick="togglePreCallMic()" 
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 transition-all active:scale-95 hover:bg-emerald-500/20">
                    <i id="icon-precall-mic" data-lucide="mic" class="w-3.5 h-3.5"></i>
                    <span id="text-precall-mic">Mic: Bật</span>
                </button>

                <!-- Toggle Cam truoc khi vao (An neu la cuoc goi thoai) -->
                <button type="button" id="btn-precall-cam" onclick="togglePreCallCam()" 
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 transition-all active:scale-95 hover:bg-indigo-500/20">
                    <i id="icon-precall-cam" data-lucide="video" class="w-3.5 h-3.5"></i>
                    <span id="text-precall-cam">Camera: Bật</span>
                </button>
            </div>
        </div>

        <!-- Nut hanh dong: Tra loi / Tu choi -->
        <div class="flex items-center justify-center gap-8 pt-1">
            <!-- Tu choi -->
            <div class="flex flex-col items-center gap-1.5">
                <button type="button" onclick="rejectIncomingCall()" 
                        class="w-14 h-14 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-lg shadow-rose-500/30 transition-transform active:scale-95" 
                        title="Từ chối">
                    <i data-lucide="phone-off" class="w-6 h-6"></i>
                </button>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Từ chối</span>
            </div>

            <!-- Tra loi -->
            <div class="flex flex-col items-center gap-1.5">
                <button type="button" onclick="acceptIncomingCall()" 
                        class="w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 transition-transform active:scale-95 animate-bounce" 
                        title="Trả lời">
                    <i data-lucide="phone" class="w-6 h-6"></i>
                </button>
                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 font-bold">Trả lời</span>
            </div>
        </div>
    </div>
</div>
