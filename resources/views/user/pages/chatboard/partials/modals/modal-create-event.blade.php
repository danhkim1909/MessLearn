<!-- MODAL: TAO LICH NHAC HEN HOC TAP -->
<div id="modal-create-event" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-lg rounded-3xl shadow-2xl flex flex-col overflow-hidden mx-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 p-5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Tạo lịch nhắc hẹn học tập</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Lên lịch học nhóm, ôn thi hoặc hẹn phòng học online</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-create-event')" class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="create-event-form" onsubmit="submitCreateEvent(event)" class="p-5 space-y-4">
            <div>
                <label for="event-title" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tiêu đề buổi hẹn <span class="text-rose-500">*</span></label>
                <input type="text" id="event-title" placeholder="Vi du: On tap Co so du lieu, Hoc nhom chuong 3..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-emerald-500 dark:text-white" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="event-remind-at" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Thời gian bắt đầu <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" id="event-remind-at" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-emerald-500 dark:text-white" required>
                </div>
                <div>
                    <label for="event-remind-before" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Báo chuông nhắc trước</label>
                    <select id="event-remind-before" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-emerald-500 dark:text-white">
                        <option value="15" selected>Trước 15 phút (chuẩn Zalo)</option>
                        <option value="0">Đúng giờ diễn ra</option>
                        <option value="30">Trước 30 phút</option>
                        <option value="60">Trước 1 giờ</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="event-location" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Địa điểm hoặc Link học Online (tùy chọn)</label>
                <div class="relative">
                    <i data-lucide="video" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="event-location" placeholder="Vi du: https://meet.google.com/abc-xyz hoac Phong thu vien B1" class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-emerald-500 dark:text-white">
                </div>
            </div>

            <div>
                <label for="event-note" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Ghi chú / Chuẩn bị trước khi học (tùy chọn)</label>
                <textarea id="event-note" rows="2.5" placeholder="Vi du: Moi nguoi nho mang theo de cuong va laptop..." class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-hidden focus:border-emerald-500 dark:text-white resize-none"></textarea>
            </div>

            <div id="create-event-error" class="hidden text-xs text-rose-500 font-medium"></div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeModal('modal-create-event')" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Hủy
                </button>
                <button type="submit" id="btn-submit-event" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    Tạo & Gửi vào nhóm
                </button>
            </div>
        </form>
    </div>
</div>
