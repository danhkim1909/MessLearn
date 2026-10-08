<!-- MODAL: QUAN LY THANH VIEN NHOM HOC TAP -->
<div id="modal-group-members" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Thành viên nhóm học tập</h3>
                    <p id="group-members-count-subtitle" class="text-xs text-slate-500 dark:text-slate-400">Đang tải danh sách...</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-group-members')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Thanh tac vu: Nut them thanh vien -->
        <div class="flex items-center justify-between gap-2 shrink-0">
            <button type="button" id="btn-toggle-add-member" onclick="toggleAddMemberPanel()" class="flex-1 py-2 px-3 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-indigo-200 dark:border-indigo-800/60">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span id="btn-toggle-add-member-text">Thêm thành viên mới</span>
            </button>
        </div>

        <!-- Khung them thanh vien moi (Hidden by default) -->
        <div id="group-add-member-panel" class="hidden p-3.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl space-y-3 shrink-0">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Chọn bạn bè để thêm vào nhóm</h4>
                <button type="button" onclick="toggleAddMemberPanel()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs">Đóng</button>
            </div>
            <div id="group-add-member-list" class="max-h-40 overflow-y-auto space-y-1.5 pr-1">
                <!-- Danh sach ban be chua co trong nhom duoc render dong -->
            </div>
            <button type="button" id="btn-submit-add-members" onclick="submitAddMembersToGroup()" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center justify-center gap-1.5">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                <span>Xác nhận thêm vào nhóm</span>
            </button>
        </div>

        <!-- Danh sach thanh vien hien tai -->
        <div class="flex-1 overflow-y-auto space-y-2 pr-1 min-h-[160px]" id="group-members-list-container">
            <div id="group-members-loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent mb-2"></div>
                <p class="text-xs text-slate-500">Đang tải danh sách thành viên...</p>
            </div>
            <div id="group-members-list" class="space-y-1.5 hidden">
                <!-- Duoc render dong qua JavaScript -->
            </div>
        </div>

        <!-- Footer: Nut roi nhom -->
        <div class="border-t border-slate-100 dark:border-slate-700/60 pt-3 flex items-center justify-between shrink-0">
            <button type="button" onclick="handleLeaveGroupClick()" class="py-2 px-3.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Rời khỏi nhóm</span>
            </button>
            <button type="button" onclick="closeModal('modal-group-members')" class="py-2 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all">
                Đóng
            </button>
        </div>
    </div>
</div>
