<!-- MODAL: BẢNG XẾP HẠNG & CHI TIẾT -->
<div id="modal-quiz-leaderboard" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full h-[90vh] max-w-4xl rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 p-6 shrink-0">
            <div>
                <h3 id="leaderboard-title" class="font-bold text-lg text-slate-900 dark:text-white">Bảng Xếp Hạng</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kết quả làm bài của các thành viên</p>
            </div>
            <button type="button" onclick="closeModal('modal-quiz-leaderboard')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/30">
            <div id="leaderboard-container" class="max-w-3xl mx-auto space-y-4">
                <div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div></div>
            </div>
        </div>
    </div>
</div>
