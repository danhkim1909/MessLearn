<!-- MODAL: BANG PHAN CONG CONG VIEC & NHIEM VU NHOM -->
<div id="modal-task-board" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity p-4">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-5xl max-h-[90vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <!-- Header Modal -->
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700/60 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Bảng phân công công việc nhóm</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Theo dõi tiến độ, phân chia nhiệm vụ và thời hạn nộp bài</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-task-board')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Thanh dieu khien & Thong ke nhanh -->
        <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-full bg-slate-200 dark:bg-slate-700 font-bold text-slate-700 dark:text-slate-300">
                    Tổng cộng: <span id="task-count-total">0</span>
                </span>
                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/40 font-bold text-amber-700 dark:text-amber-400">
                    Cần làm: <span id="task-count-todo">0</span>
                </span>
                <span class="px-2.5 py-1 rounded-full bg-sky-100 dark:bg-sky-950/40 font-bold text-sky-700 dark:text-sky-400">
                    Đang làm: <span id="task-count-progress">0</span>
                </span>
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/40 font-bold text-emerald-700 dark:text-emerald-400">
                    Hoàn thành: <span id="task-count-done">0</span>
                </span>
            </div>

            <button type="button" 
                    id="btn-toggle-create-task" 
                    onclick="toggleCreateTaskForm()" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Giao việc mới</span>
            </button>
        </div>

        <!-- Khung Form tao cong viec moi (Mac dinh an) -->
        <div id="task-create-form-wrap" class="hidden p-5 bg-indigo-50/60 dark:bg-indigo-950/20 border-b border-indigo-100 dark:border-indigo-900/40 shrink-0">
            <form id="form-create-task" onsubmit="handleCreateTask(event)" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Tieu de nhiem vu -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tiêu đề công việc <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="task-input-title" 
                               required 
                               maxlength="255" 
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 dark:text-white" 
                               placeholder="VD: Làm slide thuyết trình phần 2...">
                    </div>

                    <!-- Nguoi duoc giao -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Người thực hiện
                        </label>
                        <select id="task-select-assignee" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 dark:text-white">
                            <option value="">-- Chưa giao ai (Việc chung nhóm) --</option>
                        </select>
                    </div>

                    <!-- Han chot (Deadline) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Thời hạn (Hạn chót)
                        </label>
                        <input type="date" 
                               id="task-input-due-date" 
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 dark:text-white">
                    </div>

                    <!-- Muc do uu tien -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Mức độ ưu tiên
                        </label>
                        <select id="task-select-priority" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 dark:text-white">
                            <option value="low">Thấp</option>
                            <option value="medium" selected>Trung bình</option>
                            <option value="high">Cao</option>
                            <option value="urgent">Khẩn cấp</option>
                        </select>
                    </div>

                    <!-- Mo ta chi tiet -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Mô tả / Yêu cầu chi tiết
                        </label>
                        <textarea id="task-input-description" 
                                  rows="2" 
                                  maxlength="2000" 
                                  class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 dark:text-white" 
                                  placeholder="Ghi chú thêm về yêu cầu, nguồn tài liệu hoặc link bài tập..."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" onclick="toggleCreateTaskForm(false)" class="px-3.5 py-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold transition-colors">
                        Đóng lại
                    </button>
                    <button type="submit" id="btn-submit-task" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Tạo công việc
                    </button>
                </div>
            </form>
        </div>

        <!-- Vung Bang Kanban 3 Cot cuon duoc -->
        <div class="flex-1 overflow-y-auto p-5">
            <!-- Loading state -->
            <div id="task-board-loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent mb-2"></div>
                <p class="text-xs text-slate-500">Đang tải bảng công việc...</p>
            </div>

            <!-- Bang Kanban 3 cot -->
            <div id="task-board-columns" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 h-full min-h-[360px]">
                <!-- Cot 1: CAN LAM (To Do) -->
                <div class="bg-slate-50 dark:bg-slate-900/40 rounded-2xl p-3 border border-slate-200 dark:border-slate-700/60 flex flex-col">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">Cần làm</h4>
                        </div>
                        <span id="col-count-todo" class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-extrabold text-slate-600 dark:text-slate-300">0</span>
                    </div>
                    <div id="tasks-container-todo" class="space-y-2 flex-1 overflow-y-auto min-h-[120px]">
                        <!-- Render cac the todo -->
                    </div>
                </div>

                <!-- Cot 2: DANG LAM (In Progress) -->
                <div class="bg-slate-50 dark:bg-slate-900/40 rounded-2xl p-3 border border-slate-200 dark:border-slate-700/60 flex flex-col">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">Đang làm</h4>
                        </div>
                        <span id="col-count-in_progress" class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-extrabold text-slate-600 dark:text-slate-300">0</span>
                    </div>
                    <div id="tasks-container-in_progress" class="space-y-2 flex-1 overflow-y-auto min-h-[120px]">
                        <!-- Render cac the in_progress -->
                    </div>
                </div>

                <!-- Cot 3: DA HOAN THANH (Done) -->
                <div class="bg-slate-50 dark:bg-slate-900/40 rounded-2xl p-3 border border-slate-200 dark:border-slate-700/60 flex flex-col">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">Hoàn thành</h4>
                        </div>
                        <span id="col-count-done" class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-extrabold text-slate-600 dark:text-slate-300">0</span>
                    </div>
                    <div id="tasks-container-done" class="space-y-2 flex-1 overflow-y-auto min-h-[120px]">
                        <!-- Render cac the done -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-3.5 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-400 shrink-0">
            <span class="text-[11px]">Bấm vào nút trạng thái trên thẻ công việc để chuyển cột nhanh</span>
            <button type="button" onclick="closeModal('modal-task-board')" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-bold transition-all">
                Đóng
            </button>
        </div>
    </div>
</div>
