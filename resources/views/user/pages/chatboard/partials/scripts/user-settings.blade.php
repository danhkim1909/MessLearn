<script>
// -------------------------------------------------------------
// SCRIPT: QUAN LY CAI DAT TAI KHOAN (USER SETTINGS)
// -------------------------------------------------------------

function switchSettingsTab(tabName) {
    const tabs = ['profile', 'security', 'appearance'];
    tabs.forEach(tab => {
        const btn = document.getElementById(`tab-btn-${tab}`);
        const content = document.getElementById(`tab-content-${tab}`);

        if (tab === tabName) {
            if (btn) {
                btn.className = 'flex items-center gap-2 py-3 px-3 text-xs font-bold border-b-2 border-sky-500 text-sky-600 dark:text-sky-400 transition-colors';
            }
            if (content) {
                content.classList.remove('hidden');
            }
        } else {
            if (btn) {
                btn.className = 'flex items-center gap-2 py-3 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors';
            }
            if (content) {
                content.classList.add('hidden');
            }
        }
    });

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}

function togglePasswordVisibility(inputId, btnEl) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    if (btnEl) {
        btnEl.innerHTML = isPassword 
            ? '<i data-lucide="eye-off" class="w-4 h-4"></i>' 
            : '<i data-lucide="eye" class="w-4 h-4"></i>';
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }
}

function applyThemeMode(mode) {
    const isDark = (mode === 'dark');
    if (isDark) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }

    if (typeof Toastify === 'function') {
        Toastify({
            text: `Đã chuyển sang giao diện ${isDark ? 'Tối' : 'Sáng'}`,
            duration: 2500,
            gravity: 'top',
            position: 'right',
            style: { background: '#0ea5e9', borderRadius: '0.5rem' }
        }).showToast();
    }
}

function handleAvatarFileSelect(input) {
    if (!input || !input.files || !input.files[0]) return;

    const file = input.files[0];
    const maxSizeBytes = 2 * 1024 * 1024; // 2MB

    if (file.size > maxSizeBytes) {
        if (typeof Toastify === 'function') {
            Toastify({
                text: 'Kích thước ảnh đại diện không được vượt quá 2MB.',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                style: { background: '#f43f5e', borderRadius: '0.5rem' }
            }).showToast();
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const previewEl = document.getElementById('settings-avatar-preview');
        const initialEl = document.getElementById('settings-avatar-initial');
        const removeAvatarInput = document.getElementById('settings-remove-avatar');
        const btnRemoveAvatar = document.getElementById('btn-remove-avatar');

        if (previewEl) {
            previewEl.src = e.target.result;
            previewEl.classList.remove('hidden');
        }
        if (initialEl) {
            initialEl.classList.add('hidden');
        }
        if (removeAvatarInput) {
            removeAvatarInput.value = '0';
        }
        if (btnRemoveAvatar) {
            btnRemoveAvatar.classList.remove('hidden');
            btnRemoveAvatar.classList.add('flex');
        }
    };
    reader.readAsDataURL(file);
}

function handleRemoveAvatar() {
    const input = document.getElementById('settings-avatar-input');
    const previewEl = document.getElementById('settings-avatar-preview');
    const initialEl = document.getElementById('settings-avatar-initial');
    const removeAvatarInput = document.getElementById('settings-remove-avatar');
    const btnRemoveAvatar = document.getElementById('btn-remove-avatar');

    if (input) input.value = '';
    if (previewEl) {
        previewEl.src = '';
        previewEl.classList.add('hidden');
    }
    if (initialEl) {
        initialEl.classList.remove('hidden');
        initialEl.classList.add('flex');
    }
    if (removeAvatarInput) {
        removeAvatarInput.value = '1';
    }
    if (btnRemoveAvatar) {
        btnRemoveAvatar.classList.add('hidden');
        btnRemoveAvatar.classList.remove('flex');
    }
}

async function submitProfileForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('btn-save-profile');
    const submitText = document.getElementById('btn-save-profile-text');
    const errorContainer = document.getElementById('profile-form-errors');

    if (errorContainer) {
        errorContainer.classList.add('hidden');
        errorContainer.innerHTML = '';
    }

    if (submitBtn) submitBtn.disabled = true;
    if (submitText) submitText.innerText = 'Đang lưu...';

    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch('{{ route('app.profile.update') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            if (typeof Toastify === 'function') {
                Toastify({
                    text: data.message || 'Cập nhật thông tin thành công.',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    style: { background: '#10b981', borderRadius: '0.5rem' }
                }).showToast();
            }

            // Cap nhat DOM ngay lap tuc
            const newName = data.user.name;
            const newAvatarUrl = data.user.avatar_url;
            const firstChar = newName ? newName.charAt(0).toUpperCase() : 'U';

            // Cap nhat header
            const headerNameEl = document.getElementById('header-user-name');
            if (headerNameEl) headerNameEl.textContent = newName;

            const headerAvatarEl = document.getElementById('header-user-avatar');
            if (headerAvatarEl) {
                if (newAvatarUrl) {
                    headerAvatarEl.innerHTML = `<img src="${newAvatarUrl}" alt="${newName}" class="w-full h-full object-cover">`;
                } else {
                    headerAvatarEl.innerHTML = `<span>${firstChar}</span>`;
                }
            }

            // Cap nhat sidebar nav
            const sidebarAvatarEl = document.getElementById('sidebar-user-avatar');
            if (sidebarAvatarEl) {
                if (newAvatarUrl) {
                    sidebarAvatarEl.innerHTML = `<img src="${newAvatarUrl}" alt="${newName}" class="w-full h-full object-cover">`;
                } else {
                    sidebarAvatarEl.innerHTML = `<span>${firstChar}</span>`;
                }
            }

            // Cap nhat preview trong modal
            const initialEl = document.getElementById('settings-avatar-initial');
            if (initialEl) initialEl.textContent = firstChar;

            const btnRemoveAvatar = document.getElementById('btn-remove-avatar');
            if (btnRemoveAvatar) {
                if (newAvatarUrl) {
                    btnRemoveAvatar.classList.remove('hidden');
                    btnRemoveAvatar.classList.add('flex');
                } else {
                    btnRemoveAvatar.classList.add('hidden');
                    btnRemoveAvatar.classList.remove('flex');
                }
            }

            const removeAvatarInput = document.getElementById('settings-remove-avatar');
            if (removeAvatarInput) removeAvatarInput.value = '0';

            const avatarInput = document.getElementById('settings-avatar-input');
            if (avatarInput) avatarInput.value = '';

        } else {
            // Hien thi thong bao loi
            let errorHtml = '';
            if (data.errors) {
                Object.values(data.errors).forEach(errArray => {
                    errArray.forEach(err => {
                        errorHtml += `<p>• ${err}</p>`;
                    });
                });
            } else if (data.message) {
                errorHtml = `<p>• ${data.message}</p>`;
            }

            if (errorContainer && errorHtml) {
                errorContainer.innerHTML = errorHtml;
                errorContainer.classList.remove('hidden');
            } else if (typeof Toastify === 'function') {
                Toastify({
                    text: data.message || 'Có lỗi xảy ra khi lưu thông tin.',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    style: { background: '#f43f5e', borderRadius: '0.5rem' }
                }).showToast();
            }
        }
    } catch (err) {
        console.error('Loi cap nhat profile:', err);
        if (typeof Toastify === 'function') {
            Toastify({
                text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                style: { background: '#f43f5e', borderRadius: '0.5rem' }
            }).showToast();
        }
    } finally {
        if (submitBtn) submitBtn.disabled = false;
        if (submitText) submitText.innerText = 'Lưu thay đổi';
    }
}

async function submitPasswordForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('btn-save-password');
    const submitText = document.getElementById('btn-save-password-text');
    const errorContainer = document.getElementById('password-form-errors');

    if (errorContainer) {
        errorContainer.classList.add('hidden');
        errorContainer.innerHTML = '';
    }

    if (submitBtn) submitBtn.disabled = true;
    if (submitText) submitText.innerText = 'Đang xử lý...';

    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch('{{ route('app.profile.password') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            if (typeof Toastify === 'function') {
                Toastify({
                    text: data.message || 'Đổi mật khẩu thành công.',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    style: { background: '#10b981', borderRadius: '0.5rem' }
                }).showToast();
            }
            form.reset();
        } else {
            let errorHtml = '';
            if (data.errors) {
                Object.values(data.errors).forEach(errArray => {
                    errArray.forEach(err => {
                        errorHtml += `<p>• ${err}</p>`;
                    });
                });
            } else if (data.message) {
                errorHtml = `<p>• ${data.message}</p>`;
            }

            if (errorContainer && errorHtml) {
                errorContainer.innerHTML = errorHtml;
                errorContainer.classList.remove('hidden');
            } else if (typeof Toastify === 'function') {
                Toastify({
                    text: data.message || 'Đổi mật khẩu không thành công.',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    style: { background: '#f43f5e', borderRadius: '0.5rem' }
                }).showToast();
            }
        }
    } catch (err) {
        console.error('Loi doi mat khau:', err);
        if (typeof Toastify === 'function') {
            Toastify({
                text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                style: { background: '#f43f5e', borderRadius: '0.5rem' }
            }).showToast();
        }
    } finally {
        if (submitBtn) submitBtn.disabled = false;
        if (submitText) submitText.innerText = 'Cập nhật mật khẩu';
    }
}
</script>
