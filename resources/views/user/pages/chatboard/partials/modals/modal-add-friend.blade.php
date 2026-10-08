<!-- MODAL: TIM KET BAN BANG EMAIL -->
<div id="modal-add-friend" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Ket ban bang Email</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tim kiem tai khoan ban be de gui loi moi</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-add-friend')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form tim kiem -->
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Dia chi Email nguoi dung</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="email" id="add-friend-email" 
                               onkeydown="if(event.key === 'Enter') searchFriendByEmail()"
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-sky-500 transition-colors dark:text-white" 
                               placeholder="vd: nguyenvana@gmail.com">
                    </div>
                    <button type="button" id="btn-search-friend" onclick="searchFriendByEmail()" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 transition-all flex items-center gap-1.5 shrink-0">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Tim kiem</span>
                    </button>
                </div>
            </div>

            <!-- Khu vuc hien thi ket qua tim kiem -->
            <div id="add-friend-result-container" class="min-h-[140px] flex flex-col justify-center">
                <!-- Trang thai ban dau: Hint -->
                <div id="add-friend-hint" class="text-center py-6 px-4 border border-dashed border-slate-200 dark:border-slate-700 rounded-2xl bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Nhap chinh xac email nguoi dung va nhan <strong>Tim kiem</strong> de xem thong tin tai khoan.</p>
                </div>

                <!-- Trang thai Loading -->
                <div id="add-friend-loading" class="hidden text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-sky-500 border-t-transparent mb-2"></div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Dang tim kiem nguoi dung...</p>
                </div>

                <!-- Trang thai khong tim thay -->
                <div id="add-friend-not-found" class="hidden text-center py-6 px-4 border border-rose-200 dark:border-rose-900/40 rounded-2xl bg-rose-50/60 dark:bg-rose-950/20">
                    <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-500 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="user-x" class="w-5 h-5"></i>
                    </div>
                    <p class="text-xs font-bold text-rose-600 dark:text-rose-400" id="add-friend-not-found-text">Khong tim thay nguoi dung voi email nay.</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Vui long kiem tra lai xem ban da go dung chinh ta email chua.</p>
                </div>

                <!-- The xem truoc nguoi dung (User Preview Card) -->
                <div id="add-friend-user-card" class="hidden border border-slate-200 dark:border-slate-700 rounded-2xl p-4 bg-slate-50 dark:bg-slate-900/60 space-y-3">
                    <div id="add-friend-user-info-row" onclick="handlePreviewCardClick()" class="flex items-center gap-3 cursor-pointer group hover:bg-slate-100 dark:hover:bg-slate-800/60 p-1.5 -m-1.5 rounded-xl transition-all" title="Bấm để xem hồ sơ chi tiết">
                        <div id="add-friend-card-avatar" class="w-12 h-12 rounded-2xl bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400 flex items-center justify-center font-extrabold text-base shrink-0 shadow-inner group-hover:scale-105 transition-transform">
                            U
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 id="add-friend-card-name" class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-sky-500 transition-colors truncate">Ten nguoi dung</h4>
                            <p id="add-friend-card-email" class="text-xs text-slate-500 dark:text-slate-400 truncate">email@example.com</p>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-sky-500 transition-colors shrink-0 mr-1"></i>
                    </div>

                    <!-- Badge trang thai quan he -->
                    <div id="add-friend-card-badge-container">
                        <!-- Duoc render dong qua JavaScript -->
                    </div>

                    <!-- Nut hanh dong (Action button) -->
                    <div id="add-friend-card-action-container">
                        <!-- Duoc render dong qua JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Thong bao phu -->
            <p id="add-friend-msg" class="text-xs text-center hidden"></p>
        </div>
    </div>
</div>
