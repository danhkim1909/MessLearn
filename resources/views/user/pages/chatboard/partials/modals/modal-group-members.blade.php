<!-- MODAL: QUAN LY NHOM HOC TAP (THANH VIEN, THONG TIN, CAI DAT) -->
<div id="modal-group-members" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4 max-h-[92vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white" id="group-modal-title">Quản lý nhóm học tập</h3>
                    <p id="group-members-count-subtitle" class="text-xs text-slate-500 dark:text-slate-400">Đang tải thông tin nhóm...</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-group-members')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Thanh Tab Chuyen Doi: Thanh vien | Thong tin | Cai dat -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/60 rounded-2xl shrink-0 text-xs font-bold">
            <button type="button" id="btn-group-tab-members" onclick="switchGroupModalTab('members')" class="flex-1 py-2 px-3 rounded-xl bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-xs transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Thành viên</span>
            </button>
            <button type="button" id="btn-group-tab-info" onclick="switchGroupModalTab('info')" class="flex-1 py-2 px-3 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>Thông tin</span>
            </button>
            <button type="button" id="btn-group-tab-settings" onclick="switchGroupModalTab('settings')" class="flex-1 py-2 px-3 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                <span>Cài đặt</span>
            </button>
        </div>

        <!-- TAB 1: THANH VIEN NHOM -->
        <div id="group-tab-panel-members" class="space-y-3 flex-1 flex flex-col min-h-0">
            <!-- Thanh tac vu: Nut them thanh vien -->
            <div id="group-add-member-action-row" class="flex items-center justify-between gap-2 shrink-0">
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
        </div>

        <!-- TAB 2: THONG TIN NHOM (TEN, MO TA, AVATAR) -->
        <div id="group-tab-panel-info" class="space-y-4 flex-1 overflow-y-auto pr-1 hidden">
            <!-- Avatar Nhom -->
            <div class="flex flex-col items-center text-center p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                <div class="relative group">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden border-4 border-white dark:border-slate-800 shadow-md bg-indigo-500 text-white font-extrabold text-2xl flex items-center justify-center mb-2">
                        <img id="group-info-avatar-preview" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                        <div id="group-info-avatar-initial" class="w-full h-full flex items-center justify-center">G</div>
                    </div>
                    <label id="group-info-avatar-label" for="group-edit-avatar-input" class="absolute bottom-1 -right-1 p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow cursor-pointer transition-colors hidden" title="Đổi ảnh đại diện nhóm">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                    </label>
                    <input type="file" id="group-edit-avatar-input" accept="image/*" class="hidden" onchange="previewGroupAvatar(event)">
                </div>
                <p id="group-info-avatar-hint" class="text-[11px] text-slate-400 mt-1">Chỉ Trưởng nhóm mới có thể thay đổi ảnh đại diện</p>
            </div>

            <!-- Form Thong tin nhom -->
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tên nhóm học tập <span class="text-rose-500">*</span></label>
                    <input type="text" id="group-edit-title" maxlength="100" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:border-indigo-500 transition-colors dark:text-white" placeholder="vd: Nhóm ôn thi Cuối kỳ">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mục tiêu & Mô tả nhóm</label>
                    <textarea id="group-edit-description" rows="3" maxlength="1000" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:border-indigo-500 transition-colors dark:text-white resize-none" placeholder="Mục tiêu học tập, tài liệu hoặc nội quy của nhóm..."></textarea>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                    <span>Ngày tạo nhóm:</span>
                    <span id="group-info-created-date" class="font-semibold text-slate-600 dark:text-slate-300">Đang tải...</span>
                </div>
            </div>

            <div id="group-info-admin-actions">
                <button type="button" id="btn-save-group-info" onclick="submitUpdateGroupInfo()" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Lưu thông tin nhóm</span>
                </button>
            </div>
        </div>

        <!-- TAB 3: CAI DAT QUYEN HAN NHOM (SETTINGS) -->
        <div id="group-tab-panel-settings" class="space-y-4 flex-1 overflow-y-auto pr-1 hidden">
            <div class="space-y-3">
                <!-- 1. Che do Chi doc (Read Only) -->
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5 font-bold text-xs text-slate-900 dark:text-white">
                            <i data-lucide="message-square-off" class="w-4 h-4 text-indigo-500"></i>
                            <span>Chế độ Chỉ đọc (Kênh thông báo)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Khi bật, chỉ Trưởng nhóm mới có thể gửi tin nhắn. Thành viên khác chỉ xem bài giảng/thông báo.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                        <input type="checkbox" id="setting-group-read-only" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- 2. Quyen bat dau cuoc goi / mo phong hoc -->
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5 font-bold text-xs text-slate-900 dark:text-white">
                            <i data-lucide="video" class="w-4 h-4 text-indigo-500"></i>
                            <span>Cho phép thành viên gọi điện / mở phòng họp</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Cho phép thành viên thường chủ động đổ chuông gọi nhóm hoặc mở phòng học WebRTC.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                        <input type="checkbox" id="setting-group-allow-call" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- 3. Quyen moi thanh vien -->
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5 font-bold text-xs text-slate-900 dark:text-white">
                            <i data-lucide="user-plus" class="w-4 h-4 text-indigo-500"></i>
                            <span>Cho phép thành viên mời bạn bè</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Cho phép thành viên thường tự thêm bạn bè vào nhóm mà không cần Trưởng nhóm thao tác.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                        <input type="checkbox" id="setting-group-allow-invite" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>

            <div id="group-settings-admin-actions">
                <button type="button" id="btn-save-group-settings" onclick="submitUpdateGroupSettings()" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Lưu cài đặt quyền hạn</span>
                </button>
            </div>
            <p id="group-settings-member-notice" class="text-[11px] text-slate-400 text-center hidden">Bạn đang xem cài đặt với vai trò Thành viên (Chỉ Trưởng nhóm mới có thể thay đổi).</p>
        </div>

        <!-- Footer: Nut roi nhom & Dong -->
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
