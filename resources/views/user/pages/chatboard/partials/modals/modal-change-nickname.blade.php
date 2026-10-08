<!-- MODAL: DAT BIET DANH CHO CUOC TRO CHUYEN -->
<div id="modal-change-nickname" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="tag" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Đặt biệt danh trò chuyện</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tên gợi nhớ hiển thị riêng cho bạn</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-change-nickname')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form nhap biet danh -->
        <div class="space-y-3">
            <div>
                <label for="input-custom-nickname" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Biệt danh mong muốn
                </label>
                <div class="relative">
                    <i data-lucide="edit-3" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" 
                           id="input-custom-nickname" 
                           maxlength="100" 
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors dark:text-white" 
                           placeholder="Nhập tên gọi nhớ (để trống nếu muốn dùng tên gốc)">
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">
                    Biệt danh này chỉ có hiệu lực trên giao diện của bạn, không làm thay đổi tên thật của người dùng khác.
                </p>
            </div>
        </div>

        <!-- Footer / Action Buttons -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <button type="button" 
                    id="btn-remove-nickname" 
                    onclick="handleSaveNickname(true)" 
                    class="px-3 py-2 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl text-xs font-semibold transition-colors hidden">
                Xóa biệt danh
            </button>
            <div class="flex items-center gap-2 ml-auto">
                <button type="button" 
                        onclick="closeModal('modal-change-nickname')" 
                        class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold transition-colors">
                    Hủy
                </button>
                <button type="button" 
                        id="btn-save-nickname" 
                        onclick="handleSaveNickname(false)" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20">
                    Lưu biệt danh
                </button>
            </div>
        </div>
    </div>
</div>
