<!-- MODAL: LÀM BÀI TRẮC NGHIỆM / KHẢO SÁT -->
<div id="modal-take-quiz" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full h-[90vh] max-w-4xl rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 p-6 shrink-0">
            <div>
                <h3 id="quiz-run-title" class="font-bold text-lg text-slate-900 dark:text-white">Đang tải...</h3>
                <p id="quiz-run-desc" class="text-xs text-slate-500 dark:text-slate-400"></p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-submit-quiz" onclick="submitQuiz()" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Nộp Bài
                </button>
                <button type="button" onclick="closeModal('modal-take-quiz')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/30">
            <div class="max-w-2xl mx-auto space-y-6" id="quiz-run-container">
                <div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div></div>
            </div>
            
            <div id="quiz-result-container" class="max-w-2xl mx-auto mt-6 hidden">
                <div class="p-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-center">
                    <h4 class="text-emerald-600 dark:text-emerald-400 font-bold text-lg mb-2">Đã nộp bài thành công!</h4>
                    <p class="text-slate-600 dark:text-slate-300 text-sm">Điểm số của bạn: <span id="quiz-score" class="font-bold text-xl text-emerald-600 dark:text-emerald-400"></span></p>
                    <button type="button" onclick="closeModal('modal-take-quiz')" class="mt-4 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>
