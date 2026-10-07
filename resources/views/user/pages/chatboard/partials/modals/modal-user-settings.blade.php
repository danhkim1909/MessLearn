<!-- MODAL: CAI DAT TAI KHOAN (ACCOUNT SETTINGS) -->
<div id="modal-user-settings" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header Modal -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Cài đặt tài khoản</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Quản lý hồ sơ cá nhân, ảnh đại diện và bảo mật</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-user-settings')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-100 dark:border-slate-700/60 px-6 bg-slate-50/50 dark:bg-slate-900/20">
            <button type="button" id="tab-btn-profile" onclick="switchSettingsTab('profile')" class="flex items-center gap-2 py-3 px-3 text-xs font-bold border-b-2 border-sky-500 text-sky-600 dark:text-sky-400 transition-colors">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Hồ sơ cá nhân</span>
            </button>
            <button type="button" id="tab-btn-security" onclick="switchSettingsTab('security')" class="flex items-center gap-2 py-3 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Bảo mật & Mật khẩu</span>
            </button>
            <button type="button" id="tab-btn-appearance" onclick="switchSettingsTab('appearance')" class="flex items-center gap-2 py-3 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                <i data-lucide="palette" class="w-4 h-4"></i>
                <span>Giao diện</span>
            </button>
        </div>

        <!-- Body Noi dung Modal -->
        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            <!-- TAB 1: HO SO CA NHAN -->
            <div id="tab-content-profile" class="space-y-5">
                <form id="form-update-profile" onsubmit="submitProfileForm(event)" enctype="multipart/form-data">
                    @csrf
                    <!-- Khu vuc Anh dai dien -->
                    <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/50 mb-4">
                        <div class="relative group shrink-0">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white dark:border-slate-800 shadow-md bg-sky-500 flex items-center justify-center">
                                <img id="settings-avatar-preview" 
                                     src="{{ Auth::user()->avatar_url ?? '' }}" 
                                     alt="{{ Auth::user()->name }}"
                                     class="{{ Auth::user()->avatar ? '' : 'hidden' }} w-full h-full object-cover">
                                <div id="settings-avatar-initial" 
                                     class="{{ Auth::user()->avatar ? 'hidden' : 'flex' }} w-full h-full text-white font-extrabold text-3xl items-center justify-center select-none">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 space-y-2 text-center sm:text-left">
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">Ảnh đại diện</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Định dạng hỗ trợ: JPG, PNG, WEBP. Dung lượng tối đa: 2MB.</p>
                            
                            <input type="file" id="settings-avatar-input" name="avatar" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="handleAvatarFileSelect(this)">
                            <input type="hidden" id="settings-remove-avatar" name="remove_avatar" value="0">

                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                                <button type="button" onclick="document.getElementById('settings-avatar-input').click()" class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-all">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Tải ảnh mới</span>
                                </button>
                                <button type="button" id="btn-remove-avatar" onclick="handleRemoveAvatar()" class="{{ Auth::user()->avatar ? 'flex' : 'hidden' }} px-3 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-semibold items-center gap-1.5 transition-all">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Gỡ ảnh</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Thong bao loi Profile -->
                    <div id="profile-form-errors" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-xs text-rose-600 dark:text-rose-400 space-y-1 mb-3"></div>

                    <!-- Ho va ten -->
                    <div class="space-y-1.5 mb-3">
                        <label for="settings-name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Họ và tên</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" id="settings-name" name="name" value="{{ Auth::user()->name }}" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 transition-colors">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5 mb-4">
                        <label for="settings-email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Địa chỉ Email</label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="email" id="settings-email" name="email" value="{{ Auth::user()->email }}" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 transition-colors">
                        </div>
                    </div>

                    <!-- Nut luu thay doi -->
                    <div class="flex justify-end pt-2">
                        <button type="submit" id="btn-save-profile" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-2 transition-all">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span id="btn-save-profile-text">Lưu thay đổi</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: BAO MAT & MAT KHAU -->
            <div id="tab-content-security" class="hidden space-y-4">
                <form id="form-update-password" onsubmit="submitPasswordForm(event)">
                    @csrf
                    <!-- Thong bao loi Password -->
                    <div id="password-form-errors" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-xs text-rose-600 dark:text-rose-400 space-y-1 mb-3"></div>

                    <!-- Mat khau hien tai -->
                    <div class="space-y-1.5 mb-3">
                        <label for="settings-current-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Mật khẩu hiện tại</label>
                        <div class="relative">
                            <i data-lucide="key-round" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="password" id="settings-current-password" name="current_password" required placeholder="Nhập mật khẩu đang dùng" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-10 py-2.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 transition-colors">
                            <button type="button" onclick="togglePasswordVisibility('settings-current-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mat khau moi -->
                    <div class="space-y-1.5 mb-3">
                        <label for="settings-new-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Mật khẩu mới</label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="password" id="settings-new-password" name="password" required minlength="8" placeholder="Tối thiểu 8 ký tự" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-10 py-2.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 transition-colors">
                            <button type="button" onclick="togglePasswordVisibility('settings-new-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Xac nhan mat khau moi -->
                    <div class="space-y-1.5 mb-4">
                        <label for="settings-confirm-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Xác nhận mật khẩu mới</label>
                        <div class="relative">
                            <i data-lucide="check-check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="password" id="settings-confirm-password" name="password_confirmation" required minlength="8" placeholder="Nhập lại mật khẩu mới" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-10 py-2.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 transition-colors">
                            <button type="button" onclick="togglePasswordVisibility('settings-confirm-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Nut doi mat khau -->
                    <div class="flex justify-end pt-2">
                        <button type="submit" id="btn-save-password" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-2 transition-all">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span id="btn-save-password-text">Cập nhật mật khẩu</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: GIAO DIEN -->
            <div id="tab-content-appearance" class="hidden space-y-4">
                <div class="space-y-2">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Chế độ hiển thị</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Chọn chủ đề giao diện bạn muốn sử dụng.</p>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button" onclick="applyThemeMode('light')" id="theme-card-light" class="p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-sky-500 flex flex-col items-center gap-2 transition-all bg-white text-slate-800">
                            <i data-lucide="sun" class="w-6 h-6 text-amber-500"></i>
                            <span class="text-xs font-bold">Giao diện Sáng</span>
                        </button>
                        <button type="button" onclick="applyThemeMode('dark')" id="theme-card-dark" class="p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-sky-500 flex flex-col items-center gap-2 transition-all bg-slate-900 text-white">
                            <i data-lucide="moon" class="w-6 h-6 text-sky-400"></i>
                            <span class="text-xs font-bold">Giao diện Tối</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
