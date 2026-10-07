<!-- MODAL: CUOC GOI DEN -->
<div id="modal-incoming-call" class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-sm rounded-3xl p-6 shadow-2xl text-center space-y-6">
        <!-- Avatar nguoi goi kem hieu ung song am -->
        <div class="relative w-24 h-24 mx-auto mt-2">
            <div class="absolute inset-0 rounded-full bg-sky-500/20 animate-ping"></div>
            <div class="absolute inset-1 rounded-full bg-sky-500/30 animate-pulse"></div>
            <div id="incoming-call-avatar" class="relative w-24 h-24 rounded-full bg-sky-100 dark:bg-sky-900/60 text-sky-600 dark:text-sky-300 flex items-center justify-center font-extrabold text-2xl shadow-inner border-2 border-white dark:border-slate-800 overflow-hidden">
                U
            </div>
        </div>

        <!-- Thong tin cuoc goi -->
        <div>
            <h3 id="incoming-call-name" class="font-bold text-lg text-slate-900 dark:text-white truncate">
                Ten nguoi goi
            </h3>
            <p id="incoming-call-type-label" class="text-xs text-sky-500 font-medium mt-1">
                Cuoc goi video den...
            </p>
        </div>

        <!-- Nut hanh dong: Tra loi / Tu choi -->
        <div class="flex items-center justify-center gap-6 pt-2">
            <!-- Tu choi -->
            <button type="button" onclick="rejectIncomingCall()" 
                    class="w-14 h-14 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-lg shadow-rose-500/30 transition-transform active:scale-95" 
                    title="Tu choi">
                <i data-lucide="phone-off" class="w-6 h-6"></i>
            </button>

            <!-- Tra loi -->
            <button type="button" onclick="acceptIncomingCall()" 
                    class="w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 transition-transform active:scale-95 animate-bounce" 
                    title="Tra loi">
                <i data-lucide="phone" class="w-6 h-6"></i>
            </button>
        </div>
    </div>
</div>
