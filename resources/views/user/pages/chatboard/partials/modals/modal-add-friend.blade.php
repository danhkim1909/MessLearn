<!-- MODAL 1: TÌM KẾT BẠN BẰNG EMAIL -->
<div id="modal-add-friend" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Kết bạn bằng Email</h3>
            <button type="button" onclick="closeModal('modal-add-friend')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nhập Email người dùng</label>
            <div class="flex gap-2">
                <input type="email" id="add-friend-email" class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500 transition-colors dark:text-white" placeholder="vd: loc@gmail.com">
                <button type="button" onclick="sendFriendRequest()" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Gửi
                </button>
            </div>
            <p id="add-friend-msg" class="text-xs mt-2 hidden"></p>
        </div>
    </div>
</div>
