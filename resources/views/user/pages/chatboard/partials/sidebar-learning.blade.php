<!-- CỘT 4: RIGHT SIDEBAR - CÔNG CỤ HỌC TẬP -->
<aside class="w-80 bg-slate-50 dark:bg-slate-900/50 hidden lg:flex flex-col shrink-0">
    <div class="p-4 border-b border-slate-200 dark:border-slate-800">
        <h2 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Không gian học tập</h2>
    </div>
    <div class="p-4 space-y-3 flex-1 overflow-y-auto">

        {{-- Tạo Quiz - đã có --}}
        <button onclick="openModal('modal-create-quiz')" class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-sky-500 hover:shadow-md hover:shadow-sky-500/10 transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="pen-tool" class="w-5 h-5"></i>
                </div>
                <div class="text-left">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Tạo Quiz</h4>
                    <p class="text-[10px] text-slate-500">Bài kiểm tra & Khảo sát</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-sky-500 transition-colors"></i>
        </button>

        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1 pt-1">Sắp có</p>

        {{-- Bảng xếp hạng - placeholder --}}
        <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-500 flex items-center justify-center">
                    <i data-lucide="trophy" class="w-5 h-5"></i>
                </div>
                <div class="text-left">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Bảng xếp hạng</h4>
                    <p class="text-[10px] text-slate-500">Xếp hạng tuần theo điểm</p>
                </div>
            </div>
            <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
        </button>

        {{-- Voice Note - placeholder --}}
        <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-500 flex items-center justify-center">
                    <i data-lucide="mic" class="w-5 h-5"></i>
                </div>
                <div class="text-left">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Ghi âm Voice Note</h4>
                    <p class="text-[10px] text-slate-500">Gửi ghi âm thoại vào chat</p>
                </div>
            </div>
            <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
        </button>

        {{-- Vẽ lên ảnh - placeholder --}}
        <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-500 flex items-center justify-center">
                    <i data-lucide="image" class="w-5 h-5"></i>
                </div>
                <div class="text-left">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Vẽ lên ảnh</h4>
                    <p class="text-[10px] text-slate-500">Chú thích ảnh bằng Canvas</p>
                </div>
            </div>
            <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
        </button>

        {{-- Lịch hẹn nhóm - placeholder --}}
        <button disabled class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl opacity-60 cursor-not-allowed">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <div class="text-left">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Lịch hẹn nhóm</h4>
                    <p class="text-[10px] text-slate-500">Đặt lịch học & sự kiện</p>
                </div>
            </div>
            <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Sắp có</span>
        </button>

        {{-- Mini-game --}}
        <button type="button" onclick="openModal('modal-mini-games')" class="w-full flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-indigo-500 hover:shadow-md hover:shadow-indigo-500/10 transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="gamepad-2" class="w-5 h-5"></i>
                </div>
                <div class="text-left">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Mini-game</h4>
                    <p class="text-[10px] text-slate-500">Xúc xắc, Oẳn tù tì...</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
        </button>

    </div>
</aside>
