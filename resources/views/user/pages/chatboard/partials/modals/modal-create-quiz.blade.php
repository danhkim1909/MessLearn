<!-- MODAL: TẠO FORM CUSTOM -->
<div id="modal-create-quiz" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full h-[90vh] max-w-4xl rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 p-6 shrink-0">
            <div>
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Tạo Bài Kiểm Tra / Khảo Sát</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Thiết kế form nhanh chóng chuyên biệt cho Học tập</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="submitCustomForm()" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded-xl shadow-md shadow-sky-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Lưu & Đăng vào Nhóm
                </button>
                <button type="button" onclick="closeModal('modal-create-quiz')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/30">
            <div class="max-w-2xl mx-auto space-y-6">
                <div class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm space-y-4">
                    <div>
                        <input type="text" id="quiz-title" placeholder="Tiêu đề Form (Bắt buộc)" class="w-full bg-transparent text-xl font-bold text-slate-900 dark:text-white border-b-2 border-slate-200 dark:border-slate-700 focus:border-sky-500 focus:outline-none py-2 transition-colors" required>
                    </div>
                    <div>
                        <textarea id="quiz-desc" rows="2" placeholder="Mô tả thêm (Không bắt buộc)" class="w-full bg-transparent text-sm text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700 focus:border-sky-500 focus:outline-none py-2 resize-none transition-colors"></textarea>
                    </div>
                </div>
                <div id="quiz-builder-container" class="space-y-4">
                </div>
                <div class="flex justify-center pt-2 pb-8">
                    <button type="button" onclick="addQuizQuestion()" class="px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-sky-500 hover:text-sky-500 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        Thêm Câu Hỏi Mới
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
