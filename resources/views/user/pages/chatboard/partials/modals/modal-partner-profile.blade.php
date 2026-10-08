<!-- MODAL: HO SO DOI PHUONG & QUYEN RIENG TU -->
<div id="modal-partner-profile" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-5 flex flex-col">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                    <i data-lucide="user-round" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Thông tin đối phương</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Hồ sơ cá nhân và quyền riêng tư</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-partner-profile')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Loading spinner -->
        <div id="partner-profile-loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-sky-500 border-t-transparent mb-2"></div>
            <p class="text-xs text-slate-500">Đang tải thông tin...</p>
        </div>

        <!-- Noi dung ho so (An khi loading) -->
        <div id="partner-profile-content" class="space-y-4 hidden">
            <!-- Thong tin ca nhan co ban -->
            <div class="flex flex-col items-center text-center p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-white dark:border-slate-800 shadow-md bg-sky-500 flex items-center justify-center mb-3">
                    <img id="partner-profile-avatar" src="" alt="" class="w-full h-full object-cover hidden">
                    <div id="partner-profile-initial" class="w-full h-full text-white font-extrabold text-2xl flex items-center justify-center select-none">U</div>
                </div>
                <h4 id="partner-profile-name" class="font-bold text-base text-slate-900 dark:text-white mb-0.5">Họ và tên</h4>
                <p id="partner-profile-email" class="text-xs text-slate-500 dark:text-slate-400 mb-2">email@example.com</p>
                <div id="partner-profile-badges" class="flex items-center gap-2">
                    <span id="partner-badge-friendship" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Chưa kết bạn</span>
                    <span id="partner-badge-blocked" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 hidden">Đang chặn</span>
                </div>
            </div>

            <!-- Chi tiet bo sung: Ngay tham gia -->
            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 rounded-xl space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                    <span class="flex items-center gap-1.5 text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>Thành viên từ:</span>
                    </span>
                    <span id="partner-profile-joined-date" class="font-semibold text-slate-700 dark:text-slate-200">01/01/2026</span>
                </div>
            </div>

            <!-- Khung tac vu tuong tac: Nhan tin / Ket ban / Go ket ban / Chan -->
            <div class="space-y-2 pt-1">
                <!-- Nut Nhan tin truc tiep -->
                <button type="button" id="btn-partner-send-message" onclick="handlePartnerSendMessageClick()" class="w-full py-2.5 px-4 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-sky-500/20">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Nhắn tin</span>
                </button>

                <!-- Nut Ban be / Go ket ban -->
                <div id="partner-friendship-action-container">
                    <button type="button" id="btn-partner-unfriend" onclick="handlePartnerUnfriendClick()" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/40 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 border border-slate-200 dark:border-slate-700">
                        <i data-lucide="user-minus" class="w-4 h-4"></i>
                        <span>Hủy kết bạn</span>
                    </button>
                    <button type="button" id="btn-partner-add-friend" onclick="handlePartnerAddFriendClick()" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 border border-slate-200 dark:border-slate-700">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Gửi lời mời kết bạn</span>
                    </button>
                </div>

                <!-- Nut Chan / Bo chan -->
                <div id="partner-block-action-container">
                    <button type="button" id="btn-partner-block" onclick="handlePartnerBlockClick()" class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 border border-rose-200 dark:border-rose-800/60">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        <span>Chặn người dùng này</span>
                    </button>
                    <button type="button" id="btn-partner-unblock" onclick="handlePartnerUnblockClick()" class="w-full py-2.5 px-4 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 border border-emerald-200 dark:border-emerald-800/60">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Bỏ chặn người dùng</span>
                    </button>
                </div>
                <p id="partner-block-note" class="text-[11px] text-slate-400 text-center pt-1">Khi chặn, đối phương sẽ không thể nhắn tin hay gọi điện cho bạn.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-slate-100 dark:border-slate-700/60 pt-3 flex justify-end shrink-0">
            <button type="button" onclick="closeModal('modal-partner-profile')" class="py-2 px-5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all">
                Đóng
            </button>
        </div>
    </div>
</div>
