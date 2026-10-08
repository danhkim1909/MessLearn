<script>
// Generic Modals & Friend / Group Management
function openModal(id) {
    const modalEl = document.getElementById(id);
    if (!modalEl) return;
    modalEl.classList.remove('hidden');

    if (id === 'modal-add-friend') {
        resetAddFriendModal();
    } else if (id === 'modal-create-group') {
        resetCreateGroupModal();
    } else if (id === 'modal-create-quiz') {
        const container = document.getElementById('quiz-builder-container');
        if (container && container.children.length === 0) {
            addQuizQuestion();
        }
    } else if (id === 'modal-user-settings') {
        if (typeof switchSettingsTab === 'function') {
            switchSettingsTab('profile');
        }
        const pErr = document.getElementById('profile-form-errors');
        if (pErr) { pErr.classList.add('hidden'); pErr.innerHTML = ''; }
        const pwErr = document.getElementById('password-form-errors');
        if (pwErr) { pwErr.classList.add('hidden'); pwErr.innerHTML = ''; }
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}

function closeModal(id) {
    const modalEl = document.getElementById(id);
    if (modalEl) {
        modalEl.classList.add('hidden');
    }
}

// ---------------------------------------------------------
// LOGIC MODAL: KET BAN (ADD FRIEND)
// ---------------------------------------------------------
let currentSearchedUser = null;

function resetAddFriendModal() {
    const emailInput = document.getElementById('add-friend-email');
    if (emailInput) emailInput.value = '';

    const hintEl = document.getElementById('add-friend-hint');
    const loadingEl = document.getElementById('add-friend-loading');
    const notFoundEl = document.getElementById('add-friend-not-found');
    const userCardEl = document.getElementById('add-friend-user-card');
    const msgEl = document.getElementById('add-friend-msg');

    if (hintEl) hintEl.classList.remove('hidden');
    if (loadingEl) loadingEl.classList.add('hidden');
    if (notFoundEl) notFoundEl.classList.add('hidden');
    if (userCardEl) userCardEl.classList.add('hidden');
    if (msgEl) {
        msgEl.classList.add('hidden');
        msgEl.innerText = '';
    }
    currentSearchedUser = null;
}

async function searchFriendByEmail() {
    const emailInput = document.getElementById('add-friend-email');
    const email = emailInput ? emailInput.value.trim() : '';

    const hintEl = document.getElementById('add-friend-hint');
    const loadingEl = document.getElementById('add-friend-loading');
    const notFoundEl = document.getElementById('add-friend-not-found');
    const notFoundText = document.getElementById('add-friend-not-found-text');
    const userCardEl = document.getElementById('add-friend-user-card');
    const msgEl = document.getElementById('add-friend-msg');

    if (!email) {
        if (msgEl) {
            msgEl.innerText = 'Vui long nhap dia chi email can tim kiem.';
            msgEl.className = 'text-xs text-center text-rose-500 block';
        }
        return;
    }

    if (hintEl) hintEl.classList.add('hidden');
    if (notFoundEl) notFoundEl.classList.add('hidden');
    if (userCardEl) userCardEl.classList.add('hidden');
    if (loadingEl) loadingEl.classList.remove('hidden');
    if (msgEl) msgEl.classList.add('hidden');

    try {
        const url = '{{ route('app.friend.search') }}?email=' + encodeURIComponent(email);
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await res.json();
        if (loadingEl) loadingEl.classList.add('hidden');

        if (!res.ok || !data.found) {
            if (notFoundEl) notFoundEl.classList.remove('hidden');
            if (notFoundText) notFoundText.innerText = data.message || 'Khong tim thay nguoi dung voi email nay.';
            return;
        }

        currentSearchedUser = data;
        renderFriendResultCard(data);
    } catch (err) {
        if (loadingEl) loadingEl.classList.add('hidden');
        if (notFoundEl) notFoundEl.classList.remove('hidden');
        if (notFoundText) notFoundText.innerText = 'Loi ket noi den may chu, vui long thu lai.';
    }
}

function renderFriendResultCard(data) {
    const userCardEl = document.getElementById('add-friend-user-card');
    const avatarEl = document.getElementById('add-friend-card-avatar');
    const nameEl = document.getElementById('add-friend-card-name');
    const emailEl = document.getElementById('add-friend-card-email');
    const badgeContainer = document.getElementById('add-friend-card-badge-container');
    const actionContainer = document.getElementById('add-friend-card-action-container');

    if (!userCardEl) return;

    const user = data.user;
    const rel = data.relationship;

    if (nameEl) nameEl.innerText = user.name;
    if (emailEl) emailEl.innerText = user.email;

    if (avatarEl) {
        if (user.avatar) {
            avatarEl.innerHTML = `<img src="${user.avatar}" class="w-full h-full object-cover rounded-2xl" alt="${user.name}">`;
        } else {
            const firstLetter = (user.name && user.name.length > 0) ? user.name.charAt(0).toUpperCase() : 'U';
            avatarEl.innerText = firstLetter;
        }
    }

    let badgeHtml = '';
    let actionHtml = '';

    if (rel.status === 'self') {
        badgeHtml = `
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                <i data-lucide="user-check" class="w-4 h-4 text-sky-500"></i>
                <span>Day la tai khoan cua ban</span>
            </div>
        `;
        actionHtml = '';
    } else if (rel.status === 'friend') {
        badgeHtml = `
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>Da la ban be</span>
            </div>
        `;
        actionHtml = `
            <div class="flex gap-2">
                <button type="button" onclick="handlePartnerSendMessageClickWithId(${user.id})" class="flex-1 py-2.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Nhan tin</span>
                </button>
                <button type="button" onclick="unfriendUser(${user.id})" class="px-3 py-2.5 bg-slate-100 hover:bg-rose-500 hover:text-white dark:bg-slate-700 text-slate-500 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5" title="Huy ket ban">
                    <i data-lucide="user-minus" class="w-4 h-4"></i>
                    <span>Huy ket ban</span>
                </button>
            </div>
        `;
    } else if (rel.status === 'pending_sent') {
        badgeHtml = `
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 text-xs font-semibold">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span>Da gui loi moi ket ban (Dang cho phan hoi)</span>
            </div>
        `;
        actionHtml = `
            <div class="flex gap-2">
                <button type="button" onclick="handlePartnerSendMessageClickWithId(${user.id})" class="flex-1 py-2.5 px-4 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Nhan tin</span>
                </button>
                <button type="button" onclick="cancelFriendRequest(${rel.friendship_id})" id="btn-cancel-friend-modal" class="px-3 py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                    <span>Huy loi moi</span>
                </button>
            </div>
        `;
    } else if (rel.status === 'pending_received') {
        badgeHtml = `
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 text-xs font-semibold">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Nguoi nay da gui loi moi ket ban cho ban</span>
            </div>
        `;
        actionHtml = `
            <div class="space-y-2">
                <div class="flex gap-2">
                    <button type="button" onclick="acceptFriendFromModal(${rel.friendship_id})" id="btn-accept-friend-modal" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Chap nhan</span>
                    </button>
                    <button type="button" onclick="rejectFriendFromModal(${rel.friendship_id})" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-rose-500 hover:text-white text-slate-600 dark:text-slate-300 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        <span>Tu choi</span>
                    </button>
                </div>
                <button type="button" onclick="handlePartnerSendMessageClickWithId(${user.id})" class="w-full py-2 px-3 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Nhan tin truc tiep</span>
                </button>
            </div>
        `;
    } else if (rel.status === 'blocked') {
        badgeHtml = `
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 text-xs font-semibold">
                <i data-lucide="ban" class="w-4 h-4"></i>
                <span>Khong the lien he voi nguoi dung nay</span>
            </div>
        `;
        actionHtml = '';
    } else {
        badgeHtml = `
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 text-xs font-semibold">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Chua ket ban</span>
            </div>
        `;
        actionHtml = `
            <div class="flex gap-2">
                <button type="button" onclick="handlePartnerSendMessageClickWithId(${user.id})" class="flex-1 py-2.5 px-4 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Nhan tin</span>
                </button>
                <button type="button" onclick="submitFriendRequest(${user.id})" id="btn-send-friend-modal" class="flex-1 py-2.5 px-4 bg-sky-500 hover:bg-sky-600 text-white rounded-xl font-bold text-xs shadow-md shadow-sky-500/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span id="btn-send-friend-text">Ket ban</span>
                </button>
            </div>
        `;
    }

    if (badgeContainer) badgeContainer.innerHTML = badgeHtml;
    if (actionContainer) actionContainer.innerHTML = actionHtml;

    userCardEl.classList.remove('hidden');

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}

function handlePreviewCardClick() {
    if (!currentSearchedUser || !currentSearchedUser.user) return;
    const targetUserId = currentSearchedUser.user.id;
    closeModal('modal-add-friend');
    openPartnerProfileModal(targetUserId);
}

async function submitFriendRequest(friendId) {
    const btn = document.getElementById('btn-send-friend-modal');
    const btnText = document.getElementById('btn-send-friend-text');
    const msgEl = document.getElementById('add-friend-msg');

    if (btn) {
        btn.disabled = true;
        if (btnText) btnText.innerText = 'Dang gui loi moi...';
    }

    try {
        const res = await fetch('{{ route('app.friend.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ friend_id: friendId })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Da gui loi moi ket ban!', style: { background: '#10b981' } }).showToast();
            }
            if (currentSearchedUser) {
                currentSearchedUser.relationship.status = 'pending_sent';
                renderFriendResultCard(currentSearchedUser);
            }
        } else {
            if (msgEl) {
                msgEl.innerText = data.message || 'Loi khi gui loi moi.';
                msgEl.className = 'text-xs text-center text-rose-500 block';
            }
            if (btn) {
                btn.disabled = false;
                if (btnText) btnText.innerText = 'Gui loi moi ket ban';
            }
        }
    } catch (err) {
        if (msgEl) {
            msgEl.innerText = 'Loi ket noi toi may chu.';
            msgEl.className = 'text-xs text-center text-rose-500 block';
        }
        if (btn) {
            btn.disabled = false;
            if (btnText) btnText.innerText = 'Gui loi moi ket ban';
        }
    }
}

async function acceptFriendFromModal(friendshipId) {
    const btn = document.getElementById('btn-accept-friend-modal');
    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Dang chap nhan...';
    }

    try {
        const res = await fetch(`/app/friend/accept/${friendshipId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Da chap nhan ket ban thanh cong!', style: { background: '#10b981' } }).showToast();
            }
            if (currentSearchedUser && currentSearchedUser.relationship) {
                currentSearchedUser.relationship.status = 'friend';
                renderFriendResultCard(currentSearchedUser);
            }
            const item = document.getElementById(`pending-request-item-${friendshipId}`);
            if (item) item.remove();
            updateSidebarPendingCount(-1);
        } else {
            alert(data.message || 'Khong the chap nhan loi moi.');
            if (btn) btn.disabled = false;
        }
    } catch (err) {
        alert('Loi ket noi khi chap nhan loi moi.');
        if (btn) btn.disabled = false;
    }
}

async function cancelFriendRequest(friendshipId) {
    if (!confirm('Bạn có chắc muốn hủy lời mời kết bạn này?')) return;
    try {
        const res = await fetch(`/app/friend/cancel/${friendshipId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã hủy lời mời kết bạn.', style: { background: '#64748b' } }).showToast();
            }
            if (currentSearchedUser) {
                currentSearchedUser.relationship.status = 'none';
                currentSearchedUser.relationship.friendship_id = null;
                renderFriendResultCard(currentSearchedUser);
            }
        } else {
            alert(data.message || 'Không thể hủy lời mời.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi hủy lời mời.');
    }
}

async function rejectFriendFromModal(friendshipId) {
    if (!confirm('Bạn có chắc muốn từ chối lời mời kết bạn này?')) return;
    try {
        const res = await fetch(`/app/friend/reject/${friendshipId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã từ chối lời mời kết bạn.', style: { background: '#64748b' } }).showToast();
            }
            if (currentSearchedUser) {
                currentSearchedUser.relationship.status = 'none';
                currentSearchedUser.relationship.friendship_id = null;
                renderFriendResultCard(currentSearchedUser);
            }
            const item = document.getElementById(`pending-request-item-${friendshipId}`);
            if (item) item.remove();
            updateSidebarPendingCount(-1);
        } else {
            alert(data.message || 'Không thể từ chối lời mời.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi từ chối lời mời.');
    }
}

async function unfriendUser(friendId) {
    if (!confirm('Bạn có chắc muốn hủy kết bạn với người này?')) return;
    try {
        const res = await fetch(`/app/friend/unfriend/${friendId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã hủy kết bạn thành công.', style: { background: '#64748b' } }).showToast();
            }
            if (currentSearchedUser) {
                currentSearchedUser.relationship.status = 'none';
                currentSearchedUser.relationship.friendship_id = null;
                renderFriendResultCard(currentSearchedUser);
            }
        } else {
            alert(data.message || 'Không thể hủy kết bạn.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi hủy kết bạn.');
    }
}

async function acceptFriendFromSidebar(friendshipId) {
    try {
        const res = await fetch(`/app/friend/accept/${friendshipId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã đồng ý kết bạn!', style: { background: '#10b981' } }).showToast();
            }
            const item = document.getElementById(`pending-request-item-${friendshipId}`);
            if (item) item.remove();
            updateSidebarPendingCount(-1);
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            }
        } else {
            alert(data.message || 'Không thể chấp nhận lời mời.');
        }
    } catch (e) {
        alert('Lỗi kết nối.');
    }
}

async function rejectFriendFromSidebar(friendshipId) {
    if (!confirm('Bạn có chắc muốn từ chối lời mời này?')) return;
    try {
        const res = await fetch(`/app/friend/reject/${friendshipId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã từ chối lời mời.', style: { background: '#64748b' } }).showToast();
            }
            const item = document.getElementById(`pending-request-item-${friendshipId}`);
            if (item) item.remove();
            updateSidebarPendingCount(-1);
        } else {
            alert(data.message || 'Không thể từ chối lời mời.');
        }
    } catch (e) {
        alert('Lỗi kết nối.');
    }
}

function updateSidebarPendingCount(delta) {
    const countBadge = document.getElementById('sidebar-pending-requests-count');
    const container = document.getElementById('sidebar-pending-requests-container');
    const list = document.getElementById('sidebar-pending-requests-list');
    if (!countBadge || !container) return;

    let current = parseInt(countBadge.innerText) || 0;
    current = Math.max(0, current + delta);
    countBadge.innerText = current;

    if (current <= 0 || (list && list.children.length === 0)) {
        container.classList.add('hidden');
    } else {
        container.classList.remove('hidden');
    }
}

// ---------------------------------------------------------
// LOGIC MODAL: TAO NHOM HOC TAP (CREATE GROUP)
// ---------------------------------------------------------
const selectedGroupMembers = new Map();

function resetCreateGroupModal() {
    const nameInput = document.getElementById('group-name-input');
    const filterInput = document.getElementById('group-filter-input');
    const msgEl = document.getElementById('create-group-msg');

    if (nameInput) nameInput.value = '';
    if (filterInput) filterInput.value = '';
    if (msgEl) {
        msgEl.classList.add('hidden');
        msgEl.innerText = '';
    }

    selectedGroupMembers.clear();

    document.querySelectorAll('.group-member-checkbox').forEach(cb => {
        cb.checked = false;
    });

    filterGroupFriendsList('');
    renderSelectedGroupChips();
}

function filterGroupFriendsList(query) {
    const normalized = query.trim().toLowerCase();
    const rows = document.querySelectorAll('.group-friend-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = (row.getAttribute('data-name') || '').toLowerCase();
        const email = (row.getAttribute('data-email') || '').toLowerCase();

        if (!normalized || name.includes(normalized) || email.includes(normalized)) {
            row.classList.remove('hidden');
            visibleCount++;
        } else {
            row.classList.add('hidden');
        }
    });

    const emptySearchMsg = document.getElementById('group-friends-search-empty');
    if (emptySearchMsg) {
        if (visibleCount === 0 && rows.length > 0) {
            emptySearchMsg.classList.remove('hidden');
        } else {
            emptySearchMsg.classList.add('hidden');
        }
    }
}

function toggleGroupMemberSelection(id, name, avatar) {
    const numericId = parseInt(id, 10);
    if (selectedGroupMembers.has(numericId)) {
        selectedGroupMembers.delete(numericId);
    } else {
        selectedGroupMembers.set(numericId, { id: numericId, name: name, avatar: avatar });
    }

    const checkbox = document.getElementById('group-checkbox-' + numericId);
    if (checkbox) {
        checkbox.checked = selectedGroupMembers.has(numericId);
    }

    renderSelectedGroupChips();
}

function removeGroupMemberSelection(id) {
    const numericId = parseInt(id, 10);
    selectedGroupMembers.delete(numericId);

    const checkbox = document.getElementById('group-checkbox-' + numericId);
    if (checkbox) {
        checkbox.checked = false;
    }

    renderSelectedGroupChips();
}

function renderSelectedGroupChips() {
    const chipsContainer = document.getElementById('group-selected-chips-container');
    const badgeEl = document.getElementById('group-selected-count-badge');
    if (!chipsContainer || !badgeEl) return;

    const count = selectedGroupMembers.size;
    badgeEl.innerText = `${count} thanh vien (Toi thieu 2)`;

    if (count >= 2) {
        badgeEl.className = 'text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    } else {
        badgeEl.className = 'text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
    }

    if (count === 0) {
        chipsContainer.innerHTML = `
            <span id="group-selected-empty-text" class="text-xs text-slate-400 italic px-1 select-none">
                Chua chon thanh vien nao...
            </span>
        `;
        return;
    }

    let chipsHtml = '';
    selectedGroupMembers.forEach(member => {
        const firstLetter = (member.name && member.name.length > 0) ? member.name.charAt(0).toUpperCase() : 'U';
        chipsHtml += `
            <div class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 rounded-xl px-2.5 py-1 text-xs shadow-xs animate-in fade-in duration-200">
                <div class="w-4 h-4 rounded-full bg-indigo-200 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-100 flex items-center justify-center text-[9px] font-bold">
                    ${firstLetter}
                </div>
                <span class="font-bold max-w-[100px] truncate">${member.name}</span>
                <button type="button" onclick="event.stopPropagation(); removeGroupMemberSelection(${member.id})" 
                        class="text-indigo-400 hover:text-rose-500 rounded-md transition-colors p-0.5">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            </div>
        `;
    });

    chipsContainer.innerHTML = chipsHtml;

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}

async function submitCreateGroup() {
    const nameInput = document.getElementById('group-name-input');
    const msgEl = document.getElementById('create-group-msg');
    const btn = document.getElementById('btn-create-group-submit');
    const btnText = document.getElementById('btn-create-group-text');

    const title = nameInput ? nameInput.value.trim() : '';
    const participantIds = Array.from(selectedGroupMembers.keys());

    if (!title) {
        if (msgEl) {
            msgEl.innerText = 'Vui long nhap ten nhom hoc tap.';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
        }
        if (nameInput) nameInput.focus();
        return;
    }

    if (title.length < 2) {
        if (msgEl) {
            msgEl.innerText = 'Ten nhom hoc tap phai co it nhat 2 ky tu.';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
        }
        if (nameInput) nameInput.focus();
        return;
    }

    if (participantIds.length < 2) {
        if (msgEl) {
            msgEl.innerText = 'Vui long chon toi thieu 2 ban be de tao nhom hoc tap.';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
        }
        return;
    }

    if (btn) {
        btn.disabled = true;
        if (btnText) btnText.innerText = 'Dang tao nhom...';
    }

    try {
        const res = await fetch('{{ route('app.conversation.store-group') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                title: title,
                participant_ids: participantIds
            })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            if (msgEl) {
                msgEl.innerText = 'Tao nhom hoc tap thanh cong!';
                msgEl.className = 'text-xs mt-2 text-emerald-500 block text-center';
            }
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Da tao nhom hoc tap thanh cong!', style: { background: '#10b981' } }).showToast();
            }
            setTimeout(() => {
                closeModal('modal-create-group');
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else if (data.conversation_id) {
                    window.location.href = `/app/c/${data.conversation_id}`;
                }
            }, 800);
        } else {
            if (msgEl) {
                msgEl.innerText = data.message || 'Loi khi tao nhom chat.';
                msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
            }
            if (btn) {
                btn.disabled = false;
                if (btnText) btnText.innerText = 'Tao Nhom';
            }
        }
    } catch (err) {
        if (msgEl) {
            msgEl.innerText = 'Loi ket noi toi may chu khi tao nhom.';
            msgEl.className = 'text-xs mt-2 text-rose-500 block text-center';
        }
        if (btn) {
            btn.disabled = false;
            if (btnText) btnText.innerText = 'Tao Nhom';
        }
    }
}

// Giu alias de tuong thich nguoc neu co cho khac goi
const sendFriendRequest = submitFriendRequest;
const createGroup = submitCreateGroup;

// ---------------------------------------------------------
// LOGIC MODAL: QUAN LY THANH VIEN NHOM (GROUP MEMBERS)
// ---------------------------------------------------------
let currentGroupMembersData = null;
let selectedNewMemberIds = new Set();

async function openGroupMembersModal() {
    openModal('modal-group-members');
    switchGroupModalTab('members');
    const panel = document.getElementById('group-add-member-panel');
    if (panel) panel.classList.add('hidden');
    selectedNewMemberIds.clear();
    await loadGroupInfoAndSettings();
    await loadGroupMembers();
}

async function loadGroupMembers() {
    const loadingEl = document.getElementById('group-members-loading');
    const listEl = document.getElementById('group-members-list');
    const subtitleEl = document.getElementById('group-members-count-subtitle');

    if (loadingEl) loadingEl.classList.remove('hidden');
    if (listEl) listEl.classList.add('hidden');

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/members`);
        const data = await res.json();
        currentGroupMembersData = data;

        if (loadingEl) loadingEl.classList.add('hidden');

        if (res.ok && data.success) {
            if (subtitleEl) subtitleEl.innerText = `${data.members.length} thành viên`;

            let html = '';
            data.members.forEach(m => {
                const isSelf = m.id === data.current_user_id;
                const canKick = data.is_current_user_admin && !isSelf;

                html += `
                    <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 transition-all hover:border-slate-200 dark:hover:border-slate-700">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold flex items-center justify-center shrink-0 overflow-hidden">
                                ${m.avatar ? `<img src="${m.avatar}" class="w-full h-full object-cover" alt="${m.name}">` : `<span>${m.name.charAt(0).toUpperCase()}</span>`}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">${m.name}</h4>
                                    ${isSelf ? '<span class="text-[10px] text-slate-400 font-normal">(Bạn)</span>' : ''}
                                </div>
                                <p class="text-[10px] text-slate-400 truncate">${m.email || 'Tham gia ' + m.joined_at}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            ${m.is_admin ? `
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <i data-lucide="crown" class="w-3 h-3 text-amber-500"></i>
                                    <span>Trưởng nhóm</span>
                                </span>
                            ` : `
                                <span class="px-2 py-0.5 rounded-full text-[10px] text-slate-400 bg-slate-100 dark:bg-slate-800">
                                    Thành viên
                                </span>
                            `}

                            ${canKick ? `
                                <button type="button" onclick="removeMemberFromGroup(${m.id}, '${m.name}')" 
                                        class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors" 
                                        title="Mời rời khỏi nhóm">
                                    <i data-lucide="user-x" class="w-4 h-4"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            });

            if (listEl) {
                listEl.innerHTML = html;
                listEl.classList.remove('hidden');
                if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
            }
        } else {
            if (subtitleEl) subtitleEl.innerText = 'Không thể tải danh sách';
        }
    } catch (e) {
        if (loadingEl) loadingEl.classList.add('hidden');
        if (subtitleEl) subtitleEl.innerText = 'Lỗi kết nối';
    }
}

async function toggleAddMemberPanel() {
    const panel = document.getElementById('group-add-member-panel');
    if (!panel) return;

    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        await loadAvailableFriendsForGroup();
    } else {
        panel.classList.add('hidden');
    }
}

async function loadAvailableFriendsForGroup() {
    const listEl = document.getElementById('group-add-member-list');
    if (!listEl) return;
    listEl.innerHTML = '<p class="text-xs text-slate-400 text-center py-3">Đang tải danh sách bạn bè...</p>';
    selectedNewMemberIds.clear();

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/members/available-friends`);
        const data = await res.json();

        if (res.ok && data.success) {
            if (!data.friends || data.friends.length === 0) {
                listEl.innerHTML = '<p class="text-xs text-slate-400 text-center py-3">Tất cả bạn bè của bạn đều đã có trong nhóm.</p>';
                return;
            }

            let html = '';
            data.friends.forEach(f => {
                html += `
                    <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition-colors">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-200 font-bold text-xs flex items-center justify-center shrink-0 overflow-hidden">
                                ${f.avatar ? `<img src="${f.avatar}" class="w-full h-full object-cover" alt="${f.name}">` : `<span>${f.name.charAt(0).toUpperCase()}</span>`}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">${f.name}</p>
                                <p class="text-[10px] text-slate-400 truncate">${f.email}</p>
                            </div>
                        </div>
                        <input type="checkbox" value="${f.id}" onchange="toggleSelectNewMember(this.value, this.checked)" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                    </label>
                `;
            });
            listEl.innerHTML = html;
        } else {
            listEl.innerHTML = '<p class="text-xs text-rose-500 text-center py-2">Không thể tải danh sách bạn bè.</p>';
        }
    } catch (e) {
        listEl.innerHTML = '<p class="text-xs text-rose-500 text-center py-2">Lỗi kết nối.</p>';
    }
}

function toggleSelectNewMember(userId, isChecked) {
    const id = parseInt(userId);
    if (isChecked) {
        selectedNewMemberIds.add(id);
    } else {
        selectedNewMemberIds.delete(id);
    }
}

async function submitAddMembersToGroup() {
    if (selectedNewMemberIds.size === 0) {
        alert('Vui lòng chọn ít nhất một bạn bè để thêm vào nhóm.');
        return;
    }

    const btn = document.getElementById('btn-submit-add-members');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Đang thêm thành viên...</span>';
    }

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/members/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                user_ids: Array.from(selectedNewMemberIds)
            })
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: data.message || 'Đã thêm thành viên vào nhóm!', style: { background: '#10b981' } }).showToast();
            }
            toggleAddMemberPanel();
            await loadGroupMembers();
        } else {
            alert(data.message || 'Không thể thêm thành viên.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi thêm thành viên.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i><span>Xác nhận thêm vào nhóm</span>';
            if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        }
    }
}

async function removeMemberFromGroup(userId, userName) {
    if (!confirm(`Bạn có chắc muốn mời "${userName}" rời khỏi nhóm học tập?`)) return;

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/members/remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: data.message || 'Đã mời thành viên rời nhóm.', style: { background: '#64748b' } }).showToast();
            }
            await loadGroupMembers();
        } else {
            alert(data.message || 'Không thể xóa thành viên.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi xóa thành viên.');
    }
}

async function handleLeaveGroupClick() {
    if (!confirm('Bạn có chắc chắn muốn rời khỏi nhóm học tập này?')) return;

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/leave`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Bạn đã rời nhóm học tập.', style: { background: '#64748b' } }).showToast();
            }
            setTimeout(() => {
                window.location.href = data.redirect_url || '/app';
            }, 600);
        } else {
            alert(data.message || 'Không thể rời nhóm.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi rời nhóm.');
    }
}

let currentGroupInfoData = null;

function switchGroupModalTab(tab) {
    const tabs = ['members', 'info', 'settings'];
    tabs.forEach(t => {
        const btn = document.getElementById(`btn-group-tab-${t}`);
        const panel = document.getElementById(`group-tab-panel-${t}`);
        if (btn && panel) {
            if (t === tab) {
                btn.className = 'flex-1 py-2 px-3 rounded-xl bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-xs transition-all flex items-center justify-center gap-1.5';
                panel.classList.remove('hidden');
            } else {
                btn.className = 'flex-1 py-2 px-3 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all flex items-center justify-center gap-1.5';
                panel.classList.add('hidden');
            }
        }
    });

    if (tab === 'info' || tab === 'settings') {
        loadGroupInfoAndSettings();
    }
}

async function loadGroupInfoAndSettings() {
    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/info`);
        const data = await res.json();
        if (res.ok && data.success) {
            currentGroupInfoData = data.group;
            renderGroupInfoAndSettings(data.group);
        }
    } catch (e) {
        // Bo qua loi nhe
    }
}

function renderGroupInfoAndSettings(group) {
    const titleInput = document.getElementById('group-edit-title');
    const descInput = document.getElementById('group-edit-description');
    const dateEl = document.getElementById('group-info-created-date');
    const avatarPreview = document.getElementById('group-info-avatar-preview');
    const avatarInitial = document.getElementById('group-info-avatar-initial');
    const avatarLabel = document.getElementById('group-info-avatar-label');
    const avatarHint = document.getElementById('group-info-avatar-hint');
    const saveInfoBtn = document.getElementById('group-info-admin-actions');
    const saveSettingsBtn = document.getElementById('group-settings-admin-actions');
    const memberNotice = document.getElementById('group-settings-member-notice');

    const settingReadOnly = document.getElementById('setting-group-read-only');
    const settingAllowCall = document.getElementById('setting-group-allow-call');
    const settingAllowInvite = document.getElementById('setting-group-allow-invite');

    if (titleInput) {
        titleInput.value = group.title || '';
        titleInput.disabled = !group.is_admin;
    }
    if (descInput) {
        descInput.value = group.description || '';
        descInput.disabled = !group.is_admin;
    }
    if (dateEl) {
        dateEl.innerText = group.created_at || 'Mới tạo';
    }

    if (avatarPreview && avatarInitial) {
        if (group.avatar) {
            avatarPreview.src = group.avatar;
            avatarPreview.classList.remove('hidden');
            avatarInitial.classList.add('hidden');
        } else {
            avatarPreview.classList.add('hidden');
            avatarInitial.classList.remove('hidden');
            avatarInitial.innerText = group.title ? group.title.charAt(0).toUpperCase() : 'G';
        }
    }

    if (avatarLabel) {
        if (group.is_admin) {
            avatarLabel.classList.remove('hidden');
            if (avatarHint) avatarHint.classList.add('hidden');
        } else {
            avatarLabel.classList.add('hidden');
            if (avatarHint) avatarHint.classList.remove('hidden');
        }
    }

    if (saveInfoBtn) {
        if (group.is_admin) saveInfoBtn.classList.remove('hidden');
        else saveInfoBtn.classList.add('hidden');
    }

    if (saveSettingsBtn) {
        if (group.is_admin) saveSettingsBtn.classList.remove('hidden');
        else saveSettingsBtn.classList.add('hidden');
    }

    if (memberNotice) {
        if (group.is_admin) memberNotice.classList.add('hidden');
        else memberNotice.classList.remove('hidden');
    }

    if (settingReadOnly) {
        settingReadOnly.checked = !!group.settings.read_only;
        settingReadOnly.disabled = !group.is_admin;
    }
    if (settingAllowCall) {
        settingAllowCall.checked = !!group.settings.allow_member_start_call;
        settingAllowCall.disabled = !group.is_admin;
    }
    if (settingAllowInvite) {
        settingAllowInvite.checked = !!group.settings.allow_member_invite;
        settingAllowInvite.disabled = !group.is_admin;
    }

    const addMemberActionRow = document.getElementById('group-add-member-action-row');
    if (addMemberActionRow) {
        if (!group.settings.allow_member_invite && !group.is_admin) {
            addMemberActionRow.classList.add('hidden');
        } else {
            addMemberActionRow.classList.remove('hidden');
        }
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function previewGroupAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('group-info-avatar-preview');
        const initial = document.getElementById('group-info-avatar-initial');
        if (preview && initial) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            initial.classList.add('hidden');
        }
    };
    reader.readAsDataURL(file);
}

async function submitUpdateGroupInfo() {
    const titleInput = document.getElementById('group-edit-title');
    const descInput = document.getElementById('group-edit-description');
    const avatarInput = document.getElementById('group-edit-avatar-input');
    const btn = document.getElementById('btn-save-group-info');

    const title = titleInput ? titleInput.value.trim() : '';
    if (!title) {
        alert('Vui lòng nhập tên nhóm học tập.');
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    if (descInput) formData.append('description', descInput.value.trim());
    if (avatarInput && avatarInput.files[0]) {
        formData.append('avatar', avatarInput.files[0]);
    }

    if (btn) btn.disabled = true;

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/info`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã cập nhật thông tin nhóm thành công!', style: { background: '#10b981' } }).showToast();
            }
            const headerTitle = document.getElementById('chat-header-group-title');
            if (headerTitle && data.group.title) {
                headerTitle.innerText = data.group.title;
            }
            await loadGroupInfoAndSettings();
        } else {
            alert(data.message || 'Không thể cập nhật thông tin nhóm.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi cập nhật thông tin nhóm.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function submitUpdateGroupSettings() {
    const settingReadOnly = document.getElementById('setting-group-read-only');
    const settingAllowCall = document.getElementById('setting-group-allow-call');
    const settingAllowInvite = document.getElementById('setting-group-allow-invite');
    const btn = document.getElementById('btn-save-group-settings');

    const payload = {
        read_only: settingReadOnly ? (settingReadOnly.checked ? 1 : 0) : 0,
        allow_member_start_call: settingAllowCall ? (settingAllowCall.checked ? 1 : 0) : 1,
        allow_member_invite: settingAllowInvite ? (settingAllowInvite.checked ? 1 : 0) : 1,
    };

    if (btn) btn.disabled = true;

    try {
        const res = await fetch(`/app/conversation/{{ $activeConversation?->id ?? 0 }}/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã lưu cài đặt nhóm thành công!', style: { background: '#10b981' } }).showToast();
            }
            await loadGroupInfoAndSettings();
        } else {
            alert(data.message || 'Không thể lưu cài đặt nhóm.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi lưu cài đặt nhóm.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ---------------------------------------------------------
// LOGIC HO SO DOI PHUONG & CHAN LIEN HE (PARTNER PROFILE & BLOCK)
// ---------------------------------------------------------
let currentPartnerProfileData = null;

async function openPartnerProfileModal(userId) {
    if (!userId) return;
    openModal('modal-partner-profile');

    const loadingEl = document.getElementById('partner-profile-loading');
    const contentEl = document.getElementById('partner-profile-content');

    if (loadingEl) loadingEl.classList.remove('hidden');
    if (contentEl) contentEl.classList.add('hidden');

    try {
        const res = await fetch(`/app/user/${userId}/profile`);
        const data = await res.json();
        currentPartnerProfileData = data;

        if (loadingEl) loadingEl.classList.add('hidden');

        if (res.ok && data.success) {
            renderPartnerProfileData(data);
            if (contentEl) contentEl.classList.remove('hidden');
        } else {
            alert(data.message || 'Không thể tải thông tin người dùng.');
            closeModal('modal-partner-profile');
        }
    } catch (err) {
        if (loadingEl) loadingEl.classList.add('hidden');
        alert('Lỗi kết nối khi tải hồ sơ đối phương.');
        closeModal('modal-partner-profile');
    }
}

function renderPartnerProfileData(data) {
    const user = data.user;
    const nameEl = document.getElementById('partner-profile-name');
    const emailEl = document.getElementById('partner-profile-email');
    const joinedEl = document.getElementById('partner-profile-joined-date');
    const avatarImg = document.getElementById('partner-profile-avatar');
    const initialEl = document.getElementById('partner-profile-initial');
    const friendBadge = document.getElementById('partner-badge-friendship');
    const blockBadge = document.getElementById('partner-badge-blocked');

    if (nameEl) nameEl.innerText = user.name;
    if (emailEl) emailEl.innerText = user.email;
    if (joinedEl) joinedEl.innerText = user.created_at || 'Chưa xác định';

    if (avatarImg && initialEl) {
        if (user.avatar) {
            avatarImg.src = user.avatar;
            avatarImg.classList.remove('hidden');
            initialEl.classList.add('hidden');
        } else {
            avatarImg.classList.add('hidden');
            initialEl.classList.remove('hidden');
            initialEl.innerText = user.name ? user.name.charAt(0).toUpperCase() : 'U';
        }
    }

    const friendshipContainer = document.getElementById('partner-friendship-action-container');
    const btnSendMessage = document.getElementById('btn-partner-send-message');
    const btnUnfriend = document.getElementById('btn-partner-unfriend');
    const btnAddFriend = document.getElementById('btn-partner-add-friend');
    const btnBlock = document.getElementById('btn-partner-block');
    const btnUnblock = document.getElementById('btn-partner-unblock');
    const blockNote = document.getElementById('partner-block-note');

    const isBlocked = data.is_blocked_by_me || data.is_blocked_by_them;

    if (btnSendMessage) {
        if (data.is_self || isBlocked) {
            btnSendMessage.classList.add('hidden');
        } else {
            btnSendMessage.classList.remove('hidden');
            btnSendMessage.disabled = false;
        }
    }

    if (friendBadge) {
        if (data.friend_status === 'friend') {
            friendBadge.innerText = 'Bạn bè';
            friendBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400';
        } else if (data.friend_status === 'pending_sent') {
            friendBadge.innerText = 'Đã gửi lời mời';
            friendBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400';
        } else if (data.friend_status === 'pending_received') {
            friendBadge.innerText = 'Chờ bạn phản hồi';
            friendBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400';
        } else {
            friendBadge.innerText = 'Chưa kết bạn';
            friendBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
        }
    }

    if (blockBadge) {
        if (data.is_blocked_by_me) {
            blockBadge.innerText = 'Bạn đang chặn';
            blockBadge.classList.remove('hidden');
        } else if (data.is_blocked_by_them) {
            blockBadge.innerText = 'Đối phương đã chặn bạn';
            blockBadge.classList.remove('hidden');
        } else {
            blockBadge.classList.add('hidden');
        }
    }

    if (friendshipContainer) {
        if (isBlocked || data.is_self) {
            friendshipContainer.classList.add('hidden');
        } else {
            friendshipContainer.classList.remove('hidden');
            if (data.friend_status === 'friend') {
                if (btnUnfriend) btnUnfriend.classList.remove('hidden');
                if (btnAddFriend) btnAddFriend.classList.add('hidden');
            } else if (data.friend_status === 'pending_sent') {
                if (btnUnfriend) btnUnfriend.classList.add('hidden');
                if (btnAddFriend) {
                    btnAddFriend.classList.remove('hidden');
                    btnAddFriend.disabled = true;
                    btnAddFriend.innerHTML = '<i data-lucide="clock" class="w-4 h-4"></i><span>Đã gửi lời mời kết bạn</span>';
                }
            } else if (data.friend_status === 'pending_received') {
                if (btnUnfriend) btnUnfriend.classList.add('hidden');
                if (btnAddFriend) {
                    btnAddFriend.classList.remove('hidden');
                    btnAddFriend.disabled = false;
                    btnAddFriend.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i><span>Chấp nhận lời mời kết bạn</span>';
                }
            } else {
                if (btnUnfriend) btnUnfriend.classList.add('hidden');
                if (btnAddFriend) {
                    btnAddFriend.classList.remove('hidden');
                    btnAddFriend.disabled = false;
                    btnAddFriend.innerHTML = '<i data-lucide="user-plus" class="w-4 h-4"></i><span>Gửi lời mời kết bạn</span>';
                }
            }
        }
    }

    if (btnBlock && btnUnblock) {
        if (data.is_self) {
            btnBlock.classList.add('hidden');
            btnUnblock.classList.add('hidden');
            if (blockNote) blockNote.classList.add('hidden');
        } else if (data.is_blocked_by_me) {
            btnBlock.classList.add('hidden');
            btnUnblock.classList.remove('hidden');
            if (blockNote) {
                blockNote.classList.remove('hidden');
                blockNote.innerText = 'Bạn đã chặn người dùng này. Bỏ chặn để tiếp tục liên lạc.';
            }
        } else {
            btnBlock.classList.remove('hidden');
            btnUnblock.classList.add('hidden');
            if (blockNote) {
                blockNote.classList.remove('hidden');
                blockNote.innerText = 'Khi chặn, đối phương sẽ không thể nhắn tin hay gọi điện cho bạn.';
            }
        }
    }

    updateChatBlockedUI(data.is_blocked_by_me, data.is_blocked_by_them, data.user.id);

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

async function handlePartnerBlockClick() {
    if (!currentPartnerProfileData) return;
    const user = currentPartnerProfileData.user;
    if (!confirm(`Bạn có chắc chắn muốn chặn "${user.name}"? Đối phương sẽ không thể nhắn tin hoặc gọi điện cho bạn.`)) return;

    try {
        const res = await fetch(`/app/user/${user.id}/block`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã chặn người dùng thành công.', style: { background: '#ef4444' }, duration: 3000 }).showToast();
            }
            await openPartnerProfileModal(user.id);
        } else {
            alert(data.message || 'Không thể chặn người dùng.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi thực hiện chặn.');
    }
}

async function handlePartnerUnblockClick() {
    if (!currentPartnerProfileData) return;
    const user = currentPartnerProfileData.user;
    if (!confirm(`Bạn có chắc muốn bỏ chặn "${user.name}"?`)) return;

    try {
        const res = await fetch(`/app/user/${user.id}/unblock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã bỏ chặn người dùng.', style: { background: '#10b981' }, duration: 3000 }).showToast();
            }
            await openPartnerProfileModal(user.id);
        } else {
            alert(data.message || 'Không thể bỏ chặn người dùng.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi bỏ chặn.');
    }
}

async function handlePartnerUnfriendClick() {
    if (!currentPartnerProfileData) return;
    const user = currentPartnerProfileData.user;
    if (!confirm(`Bạn có chắc chắn muốn hủy kết bạn với "${user.name}"?`)) return;

    try {
        const res = await fetch(`/app/friend/unfriend/${user.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã hủy kết bạn thành công.', style: { background: '#64748b' }, duration: 3000 }).showToast();
            }
            await openPartnerProfileModal(user.id);
        } else {
            alert(data.message || 'Không thể hủy kết bạn.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi hủy kết bạn.');
    }
}

async function handlePartnerAddFriendClick() {
    if (!currentPartnerProfileData) return;
    const user = currentPartnerProfileData.user;

    if (currentPartnerProfileData.friend_status === 'pending_received' && currentPartnerProfileData.friendship_id) {
        await acceptFriendFromSidebar(currentPartnerProfileData.friendship_id);
        await openPartnerProfileModal(user.id);
        return;
    }

    try {
        const res = await fetch('/app/friend/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ friend_id: user.id })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text: 'Đã gửi lời mời kết bạn!', style: { background: '#0284c7' }, duration: 3000 }).showToast();
            }
            await openPartnerProfileModal(user.id);
        } else {
            alert(data.message || 'Không thể gửi lời mời kết bạn.');
        }
    } catch (e) {
        alert('Lỗi kết nối khi gửi lời mời kết bạn.');
    }
}

function handlePartnerSendMessageClick() {
    if (!currentPartnerProfileData || !currentPartnerProfileData.user) return;
    handlePartnerSendMessageClickWithId(currentPartnerProfileData.user.id);
}

async function handlePartnerSendMessageClickWithId(userId) {
    if (!userId) return;
    const btn = document.getElementById('btn-partner-send-message');
    if (btn) {
        btn.disabled = true;
    }

    try {
        const res = await fetch('{{ route('app.conversation.direct') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ target_user_id: userId })
        });

        const data = await res.json();
        if (res.ok && data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
        } else {
            alert(data.message || 'Không thể tạo hoặc mở cuộc trò chuyện.');
            if (btn) btn.disabled = false;
        }
    } catch (err) {
        alert('Lỗi kết nối khi mở cuộc trò chuyện.');
        if (btn) btn.disabled = false;
    }
}

function updateChatBlockedUI(isBlockedByMe, isBlockedByThem, partnerUserId) {
    const chatForm = document.getElementById('chat-form');
    const blockedNotice = document.getElementById('chat-blocked-notice');
    const blockedMsg = document.getElementById('chat-blocked-message');
    const btnUnblock = document.getElementById('btn-unblock-from-chat');
    const btnVoice = document.getElementById('btn-header-call-voice');
    const btnVideo = document.getElementById('btn-header-call-video');

    if (!chatForm || !blockedNotice) return;

    if (isBlockedByMe || isBlockedByThem) {
        chatForm.classList.add('hidden');
        blockedNotice.classList.remove('hidden');

        if (isBlockedByMe) {
            if (blockedMsg) blockedMsg.innerText = 'Bạn đã chặn người dùng này.';
            if (btnUnblock) {
                btnUnblock.classList.remove('hidden');
                btnUnblock.setAttribute('data-user-id', partnerUserId);
            }
        } else {
            if (blockedMsg) blockedMsg.innerText = 'Bạn không thể gửi tin nhắn cho người dùng này.';
            if (btnUnblock) btnUnblock.classList.add('hidden');
        }

        if (btnVoice) {
            btnVoice.disabled = true;
            btnVoice.classList.add('opacity-40', 'cursor-not-allowed');
        }
        if (btnVideo) {
            btnVideo.disabled = true;
            btnVideo.classList.add('opacity-40', 'cursor-not-allowed');
        }
    } else {
        chatForm.classList.remove('hidden');
        blockedNotice.classList.add('hidden');

        if (btnVoice) {
            btnVoice.disabled = false;
            btnVoice.classList.remove('opacity-40', 'cursor-not-allowed');
        }
        if (btnVideo) {
            btnVideo.disabled = false;
            btnVideo.classList.remove('opacity-40', 'cursor-not-allowed');
        }
    }
}

function handleUnblockFromChatNotice() {
    const btn = document.getElementById('btn-unblock-from-chat');
    const userId = btn ? btn.getAttribute('data-user-id') : null;
    if (userId) {
        openPartnerProfileModal(userId);
    }
}

// Lang nghe su kien ban be & chan lien he thoi gian thuc tren kenh ca nhan
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.Echo !== 'undefined') {
        window.Echo.private('App.Models.User.{{ Auth::id() }}')
            .listen('.FriendshipEvent', (e) => {
                if (e.action === 'request_sent') {
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: e.message || `${e.sender.name} đã gửi lời mời kết bạn!`, style: { background: '#0284c7' }, duration: 4000 }).showToast();
                    }
                    const list = document.getElementById('sidebar-pending-requests-list');
                    if (list) {
                        const div = document.createElement('div');
                        div.id = `pending-request-item-${e.friendshipId}`;
                        div.className = 'flex items-center gap-2.5 p-2.5 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl';
                        div.innerHTML = `
                            <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0 overflow-hidden">
                                ${e.sender.avatar ? `<img src="${e.sender.avatar}" alt="${e.sender.name}" class="w-full h-full object-cover">` : `<span>${e.sender.name ? e.sender.name.charAt(0).toUpperCase() : 'U'}</span>`}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-xs text-slate-900 dark:text-white truncate">${e.sender.name}</p>
                                <p class="text-[10px] text-slate-500 truncate">${e.sender.email || ''}</p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" onclick="acceptFriendFromSidebar(${e.friendshipId})" class="p-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors" title="Chấp nhận">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="rejectFriendFromSidebar(${e.friendshipId})" class="p-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-rose-500 hover:text-white text-slate-600 dark:text-slate-300 rounded-lg transition-colors" title="Từ chối">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        `;
                        list.prepend(div);
                        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
                        updateSidebarPendingCount(1);
                    }
                } else if (e.action === 'request_accepted') {
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: e.message || `${e.sender.name} đã đồng ý kết bạn!`, style: { background: '#10b981' }, duration: 4000 }).showToast();
                    }
                } else if (e.action === 'request_canceled') {
                    const item = document.getElementById(`pending-request-item-${e.friendshipId}`);
                    if (item) {
                        item.remove();
                        updateSidebarPendingCount(-1);
                    }
                } else if (e.action === 'request_rejected') {
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: 'Lời mời kết bạn đã bị từ chối.', style: { background: '#64748b' } }).showToast();
                    }
                } else if (e.action === 'unfriended') {
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: `${e.sender.name} đã hủy kết bạn.`, style: { background: '#64748b' } }).showToast();
                    }
                } else if (e.action === 'user_blocked') {
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: `${e.sender.name} đã chặn bạn.`, style: { background: '#e11d48' }, duration: 4000 }).showToast();
                    }
                    updateChatBlockedUI(false, true, e.sender.id);
                } else if (e.action === 'user_unblocked') {
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: `${e.sender.name} đã bỏ chặn bạn.`, style: { background: '#10b981' }, duration: 4000 }).showToast();
                    }
                    updateChatBlockedUI(false, false, e.sender.id);
                }
            });
    }
});

@if(isset($activeConversation))
    @if(!$activeConversation->is_group)
        @php
            $activeChatPartner = $activeConversation->participants->where('user_id', '!=', Auth::id())->first()->user ?? null;
            $partnerBlockedByMe = $activeChatPartner ? Auth::user()->isBlocking($activeChatPartner->id) : false;
            $partnerBlockedByThem = $activeChatPartner ? Auth::user()->isBlockedBy($activeChatPartner->id) : false;
        @endphp
        document.addEventListener('DOMContentLoaded', () => {
            updateChatBlockedUI({{ $partnerBlockedByMe ? 'true' : 'false' }}, {{ $partnerBlockedByThem ? 'true' : 'false' }}, {{ $activeChatPartner?->id ?? 0 }});
        });
    @endif

    let isLoadingOlderMessages = false;
    let hasMoreOlderMessages = true;
    let oldestMessageId = 0;
    let selectedDocumentFile = null;

    function formatAudioTime(seconds) {
        if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    }

    function buildMessageHtml(message) {
        const isMine = message.user_id === {{ Auth::id() }};
        const avatarChar = (message.user && message.user.name) ? message.user.name.charAt(0).toUpperCase() : 'U';

        let timeStr = 'Vừa xong';
        if (message.created_at) {
            try {
                const dateObj = new Date(message.created_at);
                const hours = String(dateObj.getHours()).padStart(2, '0');
                const mins = String(dateObj.getMinutes()).padStart(2, '0');
                timeStr = `${hours}:${mins}`;
            } catch (e) {
                timeStr = 'Vừa xong';
            }
        }

        if (message.type === 'recalled') {
            return `
                <div id="msg-${message.id}" class="flex ${isMine ? 'justify-end' : 'justify-start'}">
                    <div class="flex gap-2 max-w-[75%] ${isMine ? 'flex-row-reverse' : 'flex-row'}">
                        ${!isMine ? `
                            <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">
                                ${avatarChar}
                            </div>
                        ` : ''}
                        <div>
                            ${!isMine ? `
                                <div class="flex items-baseline gap-2 mb-1 ml-1">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${message.user ? message.user.name : ''}</span>
                                    <span class="text-[10px] text-slate-400">${timeStr}</span>
                                </div>
                            ` : `
                                <div class="flex items-baseline gap-2 mb-1 mr-1 justify-end">
                                    <span class="text-[10px] text-slate-400">${timeStr}</span>
                                </div>
                            `}
                            <div class="border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-slate-400 dark:text-slate-500 italic px-3.5 py-2 rounded-2xl text-xs max-w-md flex items-center gap-1.5 select-none">
                                <i data-lucide="ban" class="w-3.5 h-3.5 shrink-0 opacity-70"></i>
                                <span>Tin nhắn đã được thu hồi</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        let innerContent = '';
        if (message.reply_to) {
            let replyText = message.reply_to.body;
            if (message.reply_to.type === 'quiz') {
                replyText = 'Bài kiểm tra: ' + message.reply_to.body;
            } else if (message.reply_to.type === 'audio') {
                replyText = '[Tin nhắn thoại]';
            } else if (message.reply_to.type === 'image') {
                replyText = '[Hình ảnh]' + (message.reply_to.body ? ': ' + message.reply_to.body : '');
            } else if (message.reply_to.type === 'game_dice') {
                replyText = '[Tung xúc xắc]';
            } else if (message.reply_to.type === 'game_rps') {
                replyText = '[Oẳn tù tì]';
            } else if (message.reply_to.type === 'event') {
                replyText = '[Lịch hẹn]: ' + (message.reply_to.body || '');
            } else if (message.reply_to.type === 'document') {
                replyText = '[Tài liệu]: ' + ((message.reply_to.metadata && message.reply_to.metadata.file_name) || message.reply_to.body || '');
            }
            innerContent += `
                <div onclick="scrollToMessage(${message.reply_to_id})" class="cursor-pointer hover:opacity-100 transition-all mb-2 p-2 rounded-xl ${isMine ? 'bg-black/10' : 'bg-black/5 dark:bg-white/5'} border-l-2 ${isMine ? 'border-white/50' : 'border-sky-500'} text-[11px] opacity-80">
                    <div class="font-bold mb-0.5">${message.reply_to.user ? message.reply_to.user.name : ''}</div>
                    <div class="truncate">${replyText || ''}</div>
                </div>
            `;
        }
        
        if (message.type === 'quiz') {
            innerContent += `
                <div class="flex flex-col gap-2 ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-200'}">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                        Bài kiểm tra
                    </div>
                    <p class="font-medium text-sm">${message.body}</p>
                    <div class="flex gap-2 mt-2">
                        <button type="button" onclick="openQuizRunner(${message.quiz_id || 0})" class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all ${isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white'}">
                            Bắt đầu làm bài
                        </button>
                        <button type="button" onclick="openQuizLeaderboard(${message.quiz_id || 0})" class="flex-1 text-center py-1.5 px-3 rounded-lg font-bold text-[11px] transition-all bg-amber-500 hover:bg-amber-600 text-white">
                            Xem điểm
                        </button>
                    </div>
                </div>
            `;
        } else if (message.type === 'audio') {
            const audioSrc = message.file_url || (message.file_path ? `/storage/${message.file_path}` : '');
            innerContent += `
                <div class="flex items-center gap-3 py-1 min-w-[220px]">
                    <button type="button" onclick="toggleAudioPlay(this)" class="w-8 h-8 rounded-full ${isMine ? 'bg-white text-sky-600' : 'bg-sky-500 text-white'} flex items-center justify-center shrink-0 shadow-sm transition-transform active:scale-95">
                        <i data-lucide="play" class="w-4 h-4 ml-0.5 audio-play-icon"></i>
                        <i data-lucide="pause" class="w-4 h-4 hidden audio-pause-icon"></i>
                    </button>
                    <div class="flex-1 flex flex-col justify-center">
                        <div class="w-full bg-black/10 dark:bg-white/10 h-1.5 rounded-full overflow-hidden cursor-pointer audio-progress-container" onclick="seekAudio(event, this)">
                            <div class="bg-current h-full w-0 rounded-full transition-all duration-100 audio-progress-bar"></div>
                        </div>
                        <div class="flex justify-between items-center mt-1 text-[10px] opacity-75">
                            <span class="audio-current-time">0:00</span>
                            <span class="audio-duration">--:--</span>
                        </div>
                    </div>
                    <audio src="${audioSrc}" preload="metadata" class="hidden audio-element" ontimeupdate="updateAudioProgress(this)" onloadedmetadata="initAudioDuration(this)" onended="onAudioEnded(this)"></audio>
                </div>
            `;
            if (message.body) {
                innerContent += `<p class="mt-1.5 text-xs">${(message.body || '').replace(/\n/g, "<br>")}</p>`;
            }
        } else if (message.type === 'image') {
            const imgSrc = message.file_url || (message.file_path ? `/storage/${message.file_path}` : '');
            innerContent += `
                <div class="space-y-1.5">
                    <div class="relative group/img overflow-hidden rounded-xl border border-black/5 dark:border-white/5 bg-black/5 max-w-xs sm:max-w-sm">
                        <img src="${imgSrc}" alt="Hình ảnh" class="max-h-72 w-auto max-w-full rounded-xl object-cover cursor-pointer hover:opacity-95 transition-opacity" onclick="openLightbox('${imgSrc}')" onload="if(isNearBottom(250)) smartScrollToBottom(true, true);">
                        <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover/img:opacity-100 transition-opacity bg-slate-900/75 backdrop-blur-xs p-1 rounded-lg">
                            <button type="button" onclick="openImageAnnotator('${imgSrc}', ${message.id})" class="p-1.5 text-white hover:text-sky-400 transition-colors rounded" title="Vẽ chú thích lên ảnh">
                                <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="button" onclick="openLightbox('${imgSrc}')" class="p-1.5 text-white hover:text-sky-400 transition-colors rounded" title="Xem ảnh lớn">
                                <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                    ${message.body ? `<p class="text-xs pt-1">${(message.body || '').replace(/\n/g, "<br>")}</p>` : ''}
                </div>
            `;
        } else if (message.type === 'game_dice') {
            const diceNum = (message.metadata && message.metadata.dice) ? message.metadata.dice : 1;
            innerContent += `
                <div class="flex flex-col gap-2 py-1 min-w-[200px]">
                    <div class="flex items-center gap-2 text-xs font-bold opacity-90 border-b ${isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700'} pb-1.5">
                        <i data-lucide="box" class="w-4 h-4 text-indigo-400"></i>
                        <span>Tung xúc xắc</span>
                    </div>
                    ${message.body ? `<p class="text-xs italic opacity-90">"${message.body}"</p>` : ''}
                    <div class="flex items-center gap-3 py-1">
                        <div class="w-12 h-12 rounded-2xl ${isMine ? 'bg-white text-indigo-600' : 'bg-indigo-500 text-white'} flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                            ${diceNum}
                        </div>
                        <div>
                            <div class="text-[10px] opacity-75">Kết quả ngẫu nhiên:</div>
                            <div class="font-extrabold text-sm">${diceNum} điểm</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (message.type === 'game_rps') {
            innerContent += renderRpsCardHtml(message, isMine);
        } else if (message.type === 'event') {
            innerContent += renderEventCardHtml(message, isMine);
        } else if (message.type === 'document') {
            innerContent += renderDocumentCardHtml(message, isMine);
        } else {
            innerContent += (message.body || '').replace(/\n/g, "<br>");
        }

        let replyTooltip = message.body || '';
        if (message.type === 'quiz') {
            replyTooltip = 'Bài kiểm tra: ' + (message.body || '');
        } else if (message.type === 'audio') {
            replyTooltip = '[Tin nhắn thoại]';
        } else if (message.type === 'image') {
            replyTooltip = '[Hình ảnh]' + (message.body ? ': ' + message.body : '');
        } else if (message.type === 'game_dice') {
            replyTooltip = '[Tung xúc xắc]';
        } else if (message.type === 'game_rps') {
            replyTooltip = '[Oẳn tù tì]';
        } else if (message.type === 'event') {
            replyTooltip = '[Lịch hẹn]: ' + (message.body || '');
        } else if (message.type === 'document') {
            replyTooltip = '[Tài liệu]: ' + ((message.metadata && message.metadata.file_name) || message.body || '');
        }

        return `
            <div id="msg-${message.id}" class="flex ${isMine ? 'justify-end' : 'justify-start'}">
                <div class="flex gap-2 max-w-[75%] ${isMine ? 'flex-row-reverse' : 'flex-row'}">
                    ${!isMine ? `
                        <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">
                            ${avatarChar}
                        </div>
                    ` : ''}
                    <div>
                        ${!isMine ? `
                            <div class="flex items-baseline gap-2 mb-1 ml-1">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${message.user ? message.user.name : ''}</span>
                                <span class="text-[10px] text-slate-400">${timeStr}</span>
                            </div>
                        ` : `
                            <div class="flex items-baseline gap-2 mb-1 mr-1 justify-end">
                                <span class="text-[10px] text-slate-400">${timeStr}</span>
                            </div>
                        `}
                        <div class="${isMine ? 'bg-sky-500 text-white rounded-tr-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-sm'} px-4 py-2.5 rounded-2xl text-xs max-w-md relative group">
                            <div class="absolute ${isMine ? 'right-full mr-2' : 'left-full ml-2'} top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                                <div class="relative reaction-picker-wrap">
                                    <button type="button" onclick="toggleReactionMenu(${message.id})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-amber-500 shadow-sm flex items-center justify-center transition-colors" title="Thả cảm xúc">
                                        <i data-lucide="smile" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <div id="reaction-menu-${message.id}" class="hidden reaction-popup absolute ${isMine ? 'right-0' : 'left-0'} bottom-full mb-1 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full shadow-lg items-center gap-1 z-30">
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'like')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-sky-500 hover:scale-125 transition-transform" title="Thích">
                                            <i data-lucide="thumbs-up" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'heart')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-rose-500 hover:scale-125 transition-transform" title="Yêu thích">
                                            <i data-lucide="heart" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'laugh')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-amber-500 hover:scale-125 transition-transform" title="Haha">
                                            <i data-lucide="smile" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'wow')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-violet-500 hover:scale-125 transition-transform" title="Wow">
                                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'sad')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-blue-400 hover:scale-125 transition-transform" title="Buồn">
                                            <i data-lucide="frown" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="toggleMessageReaction(${message.id}, 'angry')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-orange-500 hover:scale-125 transition-transform" title="Phẫn nộ">
                                            <i data-lucide="flame" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" onclick="prepareReply(${message.id}, '${(message.user ? message.user.name : '').replace(/'/g, '\\\'')}', '${replyTooltip.replace(/'/g, '\\\'').replace(/\r\n|\n|\r/g, ' ').substring(0, 50)}')" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-sky-500 shadow-sm flex items-center justify-center transition-colors" title="Trả lời">
                                    <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="openForwardModal(${message.id})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-indigo-500 shadow-sm flex items-center justify-center transition-colors" title="Chuyển tiếp">
                                    <i data-lucide="forward" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="togglePinMessage(${message.id})" id="btn-pin-${message.id}" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-amber-500 shadow-sm flex items-center justify-center transition-colors" title="${message.is_pinned ? 'Bỏ ghim' : 'Ghim tin nhắn'}">
                                    <i data-lucide="pin" class="w-3.5 h-3.5 ${message.is_pinned ? 'text-amber-500 fill-amber-500' : ''}"></i>
                                </button>
                                ${isMine ? `
                                    <button type="button" onclick="confirmUnsendMessage(${message.id})" class="p-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-rose-500 shadow-sm flex items-center justify-center transition-colors" title="Gỡ tin nhắn">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                ` : ''}
                            </div>
                            <!-- Huy hieu Da ghim -->
                            <div id="pin-badge-${message.id}" class="${message.is_pinned ? 'flex' : 'hidden'} items-center gap-1 text-[10px] ${isMine ? 'text-amber-200' : 'text-amber-500 dark:text-amber-400'} font-bold mb-1.5 pb-1 border-b ${isMine ? 'border-white/20' : 'border-slate-200/60 dark:border-slate-700/60'}">
                                <i data-lucide="pin" class="w-3 h-3 fill-current"></i>
                                <span>Đã ghim</span>
                            </div>
                            ${(message.metadata && message.metadata.is_forwarded) ? `
                                <div class="flex items-center gap-1 text-[10px] ${isMine ? 'text-sky-100' : 'text-slate-400 dark:text-slate-400'} font-medium italic mb-1.5 pb-1 border-b ${isMine ? 'border-white/20' : 'border-slate-200/60 dark:border-slate-700/60'}">
                                    <i data-lucide="forward" class="w-3 h-3"></i>
                                    <span>Đã chuyển tiếp</span>
                                </div>
                            ` : ''}
                            ${innerContent}
                            <div id="reactions-bar-${message.id}" class="flex flex-wrap gap-1 mt-1.5 ${isMine ? 'justify-end' : 'justify-start'} hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Append Message to Chat (Real-time & Sent)
    function appendMessageToChat(message) {
        const chatContainer = document.getElementById('chat-messages-container');
        if(!chatContainer) return;

        const wasNearBottom = isNearBottom(150);
        
        if (document.getElementById('msg-' + message.id)) {
            updateMessageInChat(message);
            return;
        }

        const isMine = message.user_id === {{ Auth::id() }};
        const messageHtml = buildMessageHtml(message);

        // An placeholder rong neu co
        const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
        if (emptyPlaceholder) {
            emptyPlaceholder.remove();
        }

        chatContainer.insertAdjacentHTML('beforeend', messageHtml);
        if (message.reactions && message.reactions.length > 0) {
            updateReactionsBar(message.id, message.reactions);
        }
        lucide.createIcons();

        if (isMine || wasNearBottom) {
            smartScrollToBottom(true, true);
        } else {
            showScrollBottomButton(true);
        }
    }

    // Tai them tin nhan cu khi cuon len dinh (Infinite Scroll / Lazy Load)
    async function loadOlderMessages() {
        const container = document.getElementById('chat-messages-container');
        const loadingEl = document.getElementById('loading-old-messages');
        if (!container || !loadingEl || isLoadingOlderMessages || !hasMoreOlderMessages) return;

        // Tim id cua tin nhan dau tien hien co trong danh sach
        const firstMsgEl = container.querySelector('[id^="msg-"]');
        const beforeId = firstMsgEl ? parseInt(firstMsgEl.id.replace('msg-', '')) : oldestMessageId;

        if (!beforeId || beforeId <= 0) {
            hasMoreOlderMessages = false;
            return;
        }

        isLoadingOlderMessages = true;
        loadingEl.classList.remove('hidden');

        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/messages/load-more?before_id=${beforeId}&limit=25`);
            if (res.ok) {
                const data = await res.json();
                const msgs = data.messages || [];

                if (msgs.length === 0) {
                    hasMoreOlderMessages = false;
                } else {
                    const oldScrollHeight = container.scrollHeight;
                    const oldScrollTop = container.scrollTop;

                    let oldHtml = '';
                    msgs.forEach(msg => {
                        // Neu tin nhan chua co trong DOM thi moi gop vao
                        if (!document.getElementById('msg-' + msg.id)) {
                            oldHtml += buildMessageHtml(msg);
                        }
                    });

                    loadingEl.insertAdjacentHTML('afterend', oldHtml);

                    msgs.forEach(msg => {
                        if (msg.reactions && msg.reactions.length > 0) {
                            updateReactionsBar(msg.id, msg.reactions);
                        }
                    });

                    lucide.createIcons();

                    // Bao toan vi tri cuon: Bu tru do chenh lech chieu cao
                    container.scrollTop = oldScrollTop + (container.scrollHeight - oldScrollHeight);

                    hasMoreOlderMessages = !!data.has_more;
                    oldestMessageId = data.oldest_id || (msgs[0] ? msgs[0].id : 0);
                }
            } else {
                hasMoreOlderMessages = false;
            }
        } catch (err) {
            console.error('Loi tai tin nhan cu:', err);
        } finally {
            loadingEl.classList.add('hidden');
            isLoadingOlderMessages = false;
        }
    }

    // Scroll to Message with Auto Lazy-load if missing
    async function scrollToMessage(id) {
        let el = document.getElementById('msg-' + id);
        if (!el && hasMoreOlderMessages) {
            Toastify({
                text: "Đang tải thêm tin nhắn cũ để tìm tin nhắn gốc...",
                duration: 2000,
                style: { background: "#0284c7" }
            }).showToast();

            await loadOlderMessages();
            el = document.getElementById('msg-' + id);
        }

        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const bubble = el.querySelector('.max-w-md') || el.querySelector('.max-w-xs');
            if (bubble) {
                bubble.classList.add('ring-4', 'ring-amber-300', 'dark:ring-amber-500', 'transition-all');
                setTimeout(() => {
                    bubble.classList.remove('ring-4', 'ring-amber-300', 'dark:ring-amber-500');
                }, 1500);
            }
        } else {
            Toastify({
                text: "Tin nhắn gốc chưa được tải hoặc ở quá xa trong lịch sử trò chuyện",
                duration: 3000,
                style: { background: "#f59e0b" }
            }).showToast();
        }
    }

    function prepareReply(id, name, text) {
        document.getElementById('reply-to-id').value = id;
        document.getElementById('reply-to-name').innerText = name;
        document.getElementById('reply-to-text').innerText = text;
        document.getElementById('reply-preview-container').classList.remove('hidden');
        document.getElementById('chat-input').focus();
    }

    function cancelReply() {
        document.getElementById('reply-to-id').value = '';
        document.getElementById('reply-preview-container').classList.add('hidden');
    }

    async function sendChatMessage(e) {
        if (e) e.preventDefault();

        const input = document.getElementById('chat-input');
        const replyInput = document.getElementById('reply-to-id');
        const text = input.value.trim();
        const replyToId = replyInput.value;

        // Neu co tai lieu dang duoc chon
        if (selectedDocumentFile) {
            await submitDocumentPayload(selectedDocumentFile, text, replyToId);
            input.value = '';
            cancelReply();
            return;
        }

        // Neu co anh dang duoc chon
        const fileToSend = selectedImageFile;
        const caption = text;

        if (fileToSend) {
            await submitImagePayload(fileToSend, caption, replyToId);
            input.value = '';
            cancelReply();
            return;
        }

        if (!text) return;

        input.value = '';
        cancelReply();
        hideTypingIndicator();

        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const payload = { body: text };
            if (replyToId) {
                payload.reply_to_id = replyToId;
            }

            const res = await fetch('{{ route('app.conversation.message.store', $activeConversation?->id ?? 0) }}', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                appendMessageToChat(data);
            } else {
                Toastify({text: "Lỗi gửi tin nhắn", style: {background: "#f43f5e"}}).showToast();
            }
        } catch (err) {
            Toastify({text: "Lỗi kết nối", style: {background: "#f43f5e"}}).showToast();
        }
    }

    function isNearBottom(threshold = 120) {
        const container = document.getElementById('chat-messages-container');
        if (!container) return true;
        return (container.scrollHeight - container.scrollTop - container.clientHeight) <= threshold;
    }

    function smartScrollToBottom(force = false, smooth = true) {
        const container = document.getElementById('chat-messages-container');
        if (!container) return;

        if (force || isNearBottom()) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
            hideScrollBottomButton();
        } else {
            showScrollBottomButton(true);
        }
    }

    function handleChatScroll() {
        const container = document.getElementById('chat-messages-container');
        if (!container) return;

        // 1. Kiem tra de an hien nut cuon xuong day
        if (isNearBottom(150)) {
            hideScrollBottomButton();
        } else {
            showScrollBottomButton(false);
        }

        // 2. Kiem tra khi cuon len dinh de tai tin nhan cu
        if (container.scrollTop <= 80 && !isLoadingOlderMessages && hasMoreOlderMessages) {
            loadOlderMessages();
        }
    }

    function showScrollBottomButton(hasNewMessage = false) {
        const btn = document.getElementById('btn-scroll-bottom');
        const badge = document.getElementById('scroll-bottom-badge');
        if (!btn) return;
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        if (badge && hasNewMessage) {
            badge.classList.remove('hidden');
        }
    }

    function hideScrollBottomButton() {
        const btn = document.getElementById('btn-scroll-bottom');
        const badge = document.getElementById('scroll-bottom-badge');
        if (!btn) return;
        btn.classList.add('hidden');
        btn.classList.remove('flex');
        if (badge) {
            badge.classList.add('hidden');
        }
    }

    // Xu ly Trang thai dang soan tin nhan (Typing Indicator)
    let lastWhisperTime = 0;
    let typingDisplayTimeout = null;

    function handleChatInputTyping() {
        const input = document.getElementById('chat-input');
        if (!input) return;

        if (input.value.trim().length === 0) {
            return;
        }

        if (typeof window.Echo !== 'undefined') {
            const now = Date.now();
            if (now - lastWhisperTime > 1500) {
                lastWhisperTime = now;
                window.Echo.private('conversation.{{ $activeConversation?->id ?? 0 }}')
                    .whisper('typing', {
                        user_id: {{ Auth::id() }},
                        user_name: '{{ addslashes(Auth::user()->name) }}'
                    });
            }
        }
    }

    function showTypingIndicator(userName) {
        const indicator = document.getElementById('typing-indicator');
        const textEl = document.getElementById('typing-indicator-text');
        const headerStatus = document.getElementById('chat-header-status');
        if (!indicator || !textEl) return;

        textEl.innerText = `${userName} đang soạn tin nhắn...`;
        indicator.classList.remove('hidden');
        indicator.classList.add('flex');

        if (headerStatus) {
            headerStatus.innerText = 'Đang soạn tin nhắn...';
            headerStatus.className = 'text-xs text-sky-500 font-medium animate-pulse';
        }

        if (isNearBottom(150)) {
            smartScrollToBottom(true, false);
        }

        if (typingDisplayTimeout) {
            clearTimeout(typingDisplayTimeout);
        }

        typingDisplayTimeout = setTimeout(() => {
            hideTypingIndicator();
        }, 3000);
    }

    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        const headerStatus = document.getElementById('chat-header-status');
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('flex');
        }
        if (headerStatus) {
            headerStatus.innerText = 'Đang hoạt động';
            headerStatus.className = 'text-xs text-emerald-500 font-medium';
        }
    }

    // Xu ly Tim kiem tin nhan (Search in Chat)
    let searchDebounceTimer = null;

    function toggleChatSearch() {
        const panel = document.getElementById('chat-search-panel');
        if (!panel) return;
        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            const input = document.getElementById('chat-search-input');
            if (input) {
                input.focus();
            }
        } else {
            closeChatSearch();
        }
    }

    function closeChatSearch() {
        const panel = document.getElementById('chat-search-panel');
        if (panel) {
            panel.classList.add('hidden');
        }
        clearChatSearchInput();
    }

    function clearChatSearchInput() {
        const input = document.getElementById('chat-search-input');
        const clearBtn = document.getElementById('btn-clear-search');
        const resultsContainer = document.getElementById('chat-search-results-container');
        const statusEl = document.getElementById('chat-search-status');

        if (input) input.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
        }
        if (statusEl) {
            statusEl.innerText = '';
            statusEl.classList.add('hidden');
        }
    }

    function escapeHtmlText(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function highlightKeyword(text, keyword) {
        if (!text) return '';
        if (!keyword) return escapeHtmlText(text);

        const escapedText = escapeHtmlText(text);
        const escapedKeyword = escapeHtmlText(keyword);
        const regex = new RegExp(`(${escapedKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escapedText.replace(regex, '<mark class="bg-amber-200 dark:bg-amber-800 text-slate-900 dark:text-white rounded px-0.5">$1</mark>');
    }

    function handleChatSearchInput(keyword) {
        const clearBtn = document.getElementById('btn-clear-search');
        const resultsContainer = document.getElementById('chat-search-results-container');
        const statusEl = document.getElementById('chat-search-status');

        const trimmed = (keyword || '').trim();
        if (clearBtn) {
            if (trimmed.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        if (trimmed.length === 0) {
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
                resultsContainer.classList.add('hidden');
            }
            if (statusEl) {
                statusEl.innerText = '';
                statusEl.classList.add('hidden');
            }
            return;
        }

        if (statusEl) {
            statusEl.innerText = 'Đang tìm kiếm...';
            statusEl.classList.remove('hidden');
        }

        searchDebounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/messages/search?q=${encodeURIComponent(trimmed)}`);
                if (res.ok) {
                    const data = await res.json();
                    const list = data.results || [];
                    renderChatSearchResults(list, trimmed);
                } else {
                    if (statusEl) statusEl.innerText = 'Lỗi máy chủ khi tìm kiếm';
                }
            } catch (err) {
                if (statusEl) statusEl.innerText = 'Lỗi kết nối khi tìm kiếm';
            }
        }, 300);
    }

    function renderChatSearchResults(results, keyword) {
        const resultsContainer = document.getElementById('chat-search-results-container');
        const statusEl = document.getElementById('chat-search-status');
        if (!resultsContainer || !statusEl) return;

        resultsContainer.innerHTML = '';

        if (results.length === 0) {
            resultsContainer.classList.add('hidden');
            statusEl.innerText = 'Không tìm thấy tin nhắn nào phù hợp.';
            statusEl.classList.remove('hidden');
            return;
        }

        statusEl.innerText = `Tìm thấy ${results.length} tin nhắn:`;
        statusEl.classList.remove('hidden');

        results.forEach(item => {
            const row = document.createElement('div');
            row.className = 'p-2 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-xl cursor-pointer transition-colors';
            row.onclick = () => jumpToSearchedMessage(item.id);

            const highlightedSnippet = highlightKeyword(item.body || '', keyword);

            row.innerHTML = `
                <div class="flex items-center justify-between text-[11px] mb-0.5">
                    <span class="font-bold text-slate-700 dark:text-slate-200">${escapeHtmlText(item.user_name)}</span>
                    <span class="text-slate-400 text-[10px]">${escapeHtmlText(item.created_at)}</span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2">
                    ${highlightedSnippet}
                </div>
            `;
            resultsContainer.appendChild(row);
        });

        resultsContainer.classList.remove('hidden');
    }

    async function jumpToSearchedMessage(targetId) {
        closeChatSearch();

        let el = document.getElementById('msg-' + targetId);
        if (!el && hasMoreOlderMessages) {
            Toastify({
                text: "Đang tải tin nhắn cũ để di chuyển tới vị trí...",
                duration: 2500,
                style: { background: "#0284c7" }
            }).showToast();

            const container = document.getElementById('chat-messages-container');
            const loadingEl = document.getElementById('loading-old-messages');
            if (container && loadingEl) {
                const firstMsgEl = container.querySelector('[id^="msg-"]');
                const beforeId = firstMsgEl ? parseInt(firstMsgEl.id.replace('msg-', '')) : oldestMessageId;

                if (beforeId && beforeId > 0) {
                    loadingEl.classList.remove('hidden');
                    try {
                        const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/messages/load-more?before_id=${beforeId}&target_id=${targetId}`);
                        if (res.ok) {
                            const data = await res.json();
                            const msgs = data.messages || [];
                            if (msgs.length > 0) {
                                const oldScrollHeight = container.scrollHeight;
                                const oldScrollTop = container.scrollTop;

                                let oldHtml = '';
                                msgs.forEach(msg => {
                                    if (!document.getElementById('msg-' + msg.id)) {
                                        oldHtml += buildMessageHtml(msg);
                                    }
                                });

                                loadingEl.insertAdjacentHTML('afterend', oldHtml);
                                msgs.forEach(msg => {
                                    if (msg.reactions && msg.reactions.length > 0) {
                                        updateReactionsBar(msg.id, msg.reactions);
                                    }
                                });
                                lucide.createIcons();

                                container.scrollTop = oldScrollTop + (container.scrollHeight - oldScrollHeight);
                                hasMoreOlderMessages = !!data.has_more;
                                oldestMessageId = data.oldest_id || (msgs[0] ? msgs[0].id : 0);
                            }
                        }
                    } catch (err) {
                        console.error('Loi khi tai tin nhan tim kiem:', err);
                    } finally {
                        loadingEl.classList.add('hidden');
                    }
                }
            }
        }

        scrollToMessage(targetId);
    }

    // Xu ly Dinh kem Tai lieu hoc tap (Document Attachments)
    function handleDocumentSelected(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 25 * 1024 * 1024) {
            Toastify({ text: "Kích thước tài liệu tối đa 25MB", style: { background: "#f43f5e" } }).showToast();
            e.target.value = '';
            return;
        }

        selectedDocumentFile = file;
        if (typeof cancelImageSelection === 'function') {
            cancelImageSelection();
        }

        const ext = file.name.split('.').pop().toLowerCase();
        let sizeHuman = '';
        if (file.size >= 1048576) {
            sizeHuman = (file.size / 1048576).toFixed(2) + ' MB';
        } else if (file.size >= 1024) {
            sizeHuman = (file.size / 1024).toFixed(1) + ' KB';
        } else {
            sizeHuman = file.size + ' B';
        }

        const nameEl = document.getElementById('document-preview-filename');
        const sizeEl = document.getElementById('document-preview-filesize');
        const iconEl = document.getElementById('doc-preview-icon');
        const iconWrap = document.getElementById('doc-preview-icon-wrap');

        if (nameEl) nameEl.innerText = file.name;
        if (sizeEl) sizeEl.innerText = sizeHuman;

        if (iconWrap && iconEl) {
            let iconName = 'file-text';
            let wrapClass = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ';
            if (ext === 'pdf') {
                iconName = 'file-text';
                wrapClass += 'bg-rose-500/10 text-rose-500';
            } else if (['doc', 'docx'].includes(ext)) {
                iconName = 'file-text';
                wrapClass += 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800';
            } else if (['xls', 'xlsx', 'csv', 'tsv'].includes(ext)) {
                iconName = 'table';
                wrapClass += 'bg-emerald-500/10 text-emerald-500';
            } else if (['ppt', 'pptx'].includes(ext)) {
                iconName = 'presentation';
                wrapClass += 'bg-amber-500/10 text-amber-500';
            } else if (['zip', 'rar', '7z'].includes(ext)) {
                iconName = 'archive';
                wrapClass += 'bg-orange-500/10 text-orange-500';
            } else if (['md', 'markdown', 'txt', 'json', 'sql', 'py', 'cpp', 'c', 'java', 'html', 'css', 'js'].includes(ext)) {
                iconName = 'file-code';
                wrapClass += 'bg-purple-500/10 text-purple-500';
            } else {
                iconName = 'file-text';
                wrapClass += 'bg-sky-500/10 text-sky-500';
            }
            iconWrap.className = wrapClass;
            iconEl.setAttribute('data-lucide', iconName);
        }

        const container = document.getElementById('document-preview-container');
        if (container) {
            container.classList.remove('hidden');
            container.classList.add('flex');
        }

        const chatInput = document.getElementById('chat-input');
        if (chatInput) chatInput.focus();
        lucide.createIcons();
    }

    function cancelDocumentSelection() {
        selectedDocumentFile = null;
        const input = document.getElementById('document-file-input');
        if (input) input.value = '';
        const container = document.getElementById('document-preview-container');
        if (container) {
            container.classList.add('hidden');
            container.classList.remove('flex');
        }
    }

    async function submitDocumentPayload(file, caption, replyToId) {
        const formData = new FormData();
        formData.append('document', file);
        if (caption) {
            formData.append('body', caption);
        }
        if (replyToId) {
            formData.append('reply_to_id', replyToId);
        }

        const headers = {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        };
        if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
            headers['X-Socket-ID'] = window.Echo.socketId();
        }

        cancelDocumentSelection();
        cancelReply();

        try {
            const res = await fetch('{{ route('app.conversation.message.store', $activeConversation?->id ?? 0) }}', {
                method: 'POST',
                headers: headers,
                body: formData
            });

            if (res.ok) {
                const data = await res.json();
                appendMessageToChat(data);
            } else {
                Toastify({ text: "Lỗi gửi tài liệu", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function renderDocumentCardHtml(message, isMine) {
        const meta = message.metadata || {};
        const fileName = meta.file_name || 'Tài liệu đính kèm';
        const ext = (meta.file_extension || fileName.split('.').pop() || '').toLowerCase();
        const fileSize = meta.file_size_human || '';
        const docUrl = message.file_url || (message.file_path ? `/storage/${message.file_path}` : '#');
        const canPreview = ['pdf', 'md', 'markdown', 'txt', 'csv', 'tsv', 'json', 'sql', 'py', 'cpp', 'c', 'java', 'html', 'css', 'js', 'log'].includes(ext);

        let iconName = 'file-text';
        let iconColor = isMine ? 'text-slate-600' : 'text-sky-500';
        let iconBg = isMine ? 'bg-white shadow-xs' : 'bg-sky-500/10 dark:bg-sky-500/20';

        if (ext === 'pdf') {
            iconName = 'file-text';
            iconColor = isMine ? 'text-rose-600' : 'text-rose-500';
            iconBg = isMine ? 'bg-white shadow-xs' : 'bg-rose-500/10 dark:bg-rose-500/20';
        } else if (['doc', 'docx'].includes(ext)) {
            iconName = 'file-text';
            iconColor = isMine ? 'text-blue-600' : 'text-blue-600 dark:text-blue-400';
            iconBg = isMine ? 'bg-white shadow-xs' : 'bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800/40';
        } else if (['xls', 'xlsx', 'csv', 'tsv'].includes(ext)) {
            iconName = 'table';
            iconColor = isMine ? 'text-emerald-600' : 'text-emerald-500';
            iconBg = isMine ? 'bg-white shadow-xs' : 'bg-emerald-500/10 dark:bg-emerald-500/20';
        } else if (['ppt', 'pptx'].includes(ext)) {
            iconName = 'presentation';
            iconColor = isMine ? 'text-amber-600' : 'text-amber-500';
            iconBg = isMine ? 'bg-white shadow-xs' : 'bg-amber-500/10 dark:bg-amber-500/20';
        } else if (['zip', 'rar', '7z'].includes(ext)) {
            iconName = 'archive';
            iconColor = isMine ? 'text-orange-600' : 'text-orange-500';
            iconBg = isMine ? 'bg-white shadow-xs' : 'bg-orange-500/10 dark:bg-orange-500/20';
        } else if (['md', 'markdown', 'txt', 'json', 'sql', 'py', 'cpp', 'c', 'java', 'html', 'css', 'js', 'log'].includes(ext)) {
            iconName = 'file-code';
            iconColor = isMine ? 'text-purple-600' : 'text-purple-500';
            iconBg = isMine ? 'bg-white shadow-xs' : 'bg-purple-500/10 dark:bg-purple-500/20';
        }

        const safeFileName = escapeHtmlText(fileName);
        const safeDocUrl = encodeURI(docUrl);
        const previewBtn = canPreview ? `
            <button type="button" onclick="openDocumentViewer('${safeDocUrl}', '${safeFileName}', '${ext}')" class="flex-1 py-1.5 px-3 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 ${isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white shadow-xs'}">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                <span>Xem trực tiếp</span>
            </button>
        ` : '';

        const downloadBtnClass = canPreview ? 'px-3 py-1.5' : 'flex-1 py-1.5 px-3';
        const downloadBtnText = canPreview ? 'Tải' : 'Tải tài liệu';

        return `
            <div class="flex flex-col gap-2 min-w-[240px] sm:min-w-[280px]">
                <div class="flex items-center gap-3 p-3 rounded-2xl ${isMine ? 'bg-black/10 border border-white/20' : 'bg-black/5 dark:bg-white/5 border border-slate-200 dark:border-slate-700/60'} transition-all">
                    <div class="w-11 h-11 rounded-xl ${iconBg} ${iconColor} flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="${iconName}" class="w-6 h-6"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-xs ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-100'} truncate" title="${safeFileName}">
                            ${safeFileName}
                        </h4>
                        <div class="flex items-center gap-2 text-[10px] ${isMine ? 'text-white/70' : 'text-slate-500 dark:text-slate-400'} mt-0.5">
                            <span class="font-medium uppercase">${ext}</span>
                            ${fileSize ? `<span>•</span><span>${fileSize}</span>` : ''}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-0.5">
                    ${previewBtn}
                    <a href="${safeDocUrl}" download="${safeFileName}" class="${downloadBtnClass} rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 ${isMine ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200'}" title="Tải tài liệu về máy">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>${downloadBtnText}</span>
                    </a>
                </div>
                ${message.body ? `<p class="text-xs pt-1 opacity-90 ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-200'}">${escapeHtmlText(message.body).replace(/\n/g, '<br>')}</p>` : ''}
            </div>
        `;
    }

    // TRINH XEM TAI LIEU TRUC TIEP (IN-APP DOCUMENT VIEWER)
    function openDocumentViewer(url, fileName, ext) {
        const modal = document.getElementById('modal-document-viewer');
        if (!modal) return;

        const filenameEl = document.getElementById('doc-viewer-filename');
        const badgeEl = document.getElementById('doc-viewer-ext-badge');
        const iconEl = document.getElementById('doc-viewer-header-icon');
        const iconWrap = document.getElementById('doc-viewer-badge-icon');
        const downloadBtn = document.getElementById('doc-viewer-download-btn');
        const fallbackDownload = document.getElementById('doc-viewer-fallback-download');

        if (filenameEl) filenameEl.innerText = fileName;
        if (badgeEl) badgeEl.innerText = (ext || 'FILE').toUpperCase();
        if (downloadBtn) {
            downloadBtn.href = url;
            downloadBtn.setAttribute('download', fileName);
        }
        if (fallbackDownload) {
            fallbackDownload.href = url;
            fallbackDownload.setAttribute('download', fileName);
        }

        let iconName = 'file-text';
        let wrapClass = 'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ';
        if (ext === 'pdf') {
            iconName = 'file-text';
            wrapClass += 'bg-rose-500/10 text-rose-500';
        } else if (['xls', 'xlsx', 'csv', 'tsv'].includes(ext)) {
            iconName = 'table';
            wrapClass += 'bg-emerald-500/10 text-emerald-500';
        } else if (['md', 'markdown', 'txt', 'json', 'sql', 'py', 'cpp', 'c', 'java', 'html', 'css', 'js', 'log'].includes(ext)) {
            iconName = 'file-code';
            wrapClass += 'bg-purple-500/10 text-purple-500';
        } else {
            iconName = 'file-text';
            wrapClass += 'bg-sky-500/10 text-sky-500';
        }
        if (iconWrap) iconWrap.className = wrapClass;
        if (iconEl) iconEl.setAttribute('data-lucide', iconName);

        const loadingEl = document.getElementById('doc-viewer-loading');
        const pdfFrame = document.getElementById('doc-viewer-pdf-frame');
        const mdWrap = document.getElementById('doc-viewer-markdown-wrap');
        const codeWrap = document.getElementById('doc-viewer-code-wrap');
        const tableWrap = document.getElementById('doc-viewer-table-wrap');
        const errorEl = document.getElementById('doc-viewer-error');

        if (loadingEl) loadingEl.classList.remove('hidden');
        if (pdfFrame) {
            pdfFrame.classList.add('hidden');
            pdfFrame.src = '';
        }
        if (mdWrap) mdWrap.classList.add('hidden');
        if (codeWrap) codeWrap.classList.add('hidden');
        if (tableWrap) tableWrap.classList.add('hidden');
        if (errorEl) errorEl.classList.add('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lucide.createIcons();

        if (ext === 'pdf') {
            if (pdfFrame) {
                pdfFrame.onload = () => {
                    if (loadingEl) loadingEl.classList.add('hidden');
                    pdfFrame.classList.remove('hidden');
                };
                pdfFrame.onerror = () => {
                    if (loadingEl) loadingEl.classList.add('hidden');
                    if (errorEl) errorEl.classList.remove('hidden');
                };
                pdfFrame.src = url;
            }
            return;
        }

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.text();
            })
            .then(content => {
                if (loadingEl) loadingEl.classList.add('hidden');

                if (ext === 'md' || ext === 'markdown') {
                    const mdContainer = document.getElementById('doc-viewer-markdown-content');
                    if (mdContainer && mdWrap) {
                        mdContainer.innerHTML = renderMarkdownToHtml(content);
                        mdWrap.classList.remove('hidden');
                    }
                } else if (ext === 'csv' || ext === 'tsv') {
                    const tableContainer = document.getElementById('doc-viewer-table-content');
                    if (tableContainer && tableWrap) {
                        tableContainer.innerHTML = renderCsvToTable(content, ext === 'tsv' ? '\t' : ',');
                        tableWrap.classList.remove('hidden');
                    }
                } else {
                    const codeEl = document.getElementById('doc-viewer-code-content');
                    const langEl = document.getElementById('doc-viewer-code-lang');
                    const linesEl = document.getElementById('doc-viewer-code-lines');
                    if (codeEl && codeWrap) {
                        codeEl.textContent = content;
                        const lineCount = content.split('\n').length;
                        if (langEl) langEl.innerText = ext.toUpperCase();
                        if (linesEl) linesEl.innerText = lineCount + ' dòng';
                        codeWrap.classList.remove('hidden');
                    }
                }
                lucide.createIcons();
            })
            .catch(() => {
                if (loadingEl) loadingEl.classList.add('hidden');
                if (errorEl) errorEl.classList.remove('hidden');
                lucide.createIcons();
            });
    }

    function closeDocumentViewer() {
        const modal = document.getElementById('modal-document-viewer');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            const pdfFrame = document.getElementById('doc-viewer-pdf-frame');
            if (pdfFrame) pdfFrame.src = '';
        }
    }

    function toggleDocViewerFullscreen() {
        const container = document.getElementById('doc-viewer-container');
        const icon = document.getElementById('doc-viewer-fs-icon');
        if (!container) return;

        const isFullscreen = container.classList.contains('w-screen');
        if (isFullscreen) {
            container.className = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl flex flex-col w-full max-w-5xl h-[88vh] overflow-hidden transition-all duration-200';
            if (icon) icon.setAttribute('data-lucide', 'maximize');
        } else {
            container.className = 'bg-white dark:bg-slate-900 border-0 rounded-none shadow-none flex flex-col w-screen h-screen max-w-none overflow-hidden transition-all duration-200';
            if (icon) icon.setAttribute('data-lucide', 'minimize');
        }
        lucide.createIcons();
    }

    function renderMarkdownToHtml(md) {
        if (!md) return '';
        let html = '';
        const lines = md.split('\n');
        let inList = false;

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];

            if (line.startsWith('# ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += `<h1 class="text-xl font-black text-slate-900 dark:text-white pb-2 border-b border-slate-200 dark:border-slate-800">${parseInlineMarkdown(line.slice(2))}</h1>`;
            } else if (line.startsWith('## ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += `<h2 class="text-lg font-extrabold text-slate-900 dark:text-white pt-2">${parseInlineMarkdown(line.slice(3))}</h2>`;
            } else if (line.startsWith('### ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += `<h3 class="text-base font-bold text-slate-800 dark:text-slate-100">${parseInlineMarkdown(line.slice(4))}</h3>`;
            } else if (line.startsWith('> ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += `<blockquote class="p-3 my-2 border-l-4 border-sky-500 bg-sky-50 dark:bg-sky-950/40 rounded-r-xl italic text-slate-700 dark:text-slate-300 text-xs">${parseInlineMarkdown(line.slice(2))}</blockquote>`;
            } else if (line.startsWith('- ') || line.startsWith('* ')) {
                if (!inList) {
                    html += '<ul class="list-disc pl-5 space-y-1 text-slate-700 dark:text-slate-300">';
                    inList = true;
                }
                html += `<li>${parseInlineMarkdown(line.slice(2))}</li>`;
            } else if (line.trim() === '') {
                if (inList) { html += '</ul>'; inList = false; }
                html += '<div class="h-2"></div>';
            } else {
                if (inList) { html += '</ul>'; inList = false; }
                html += `<p class="text-slate-700 dark:text-slate-300 leading-relaxed">${parseInlineMarkdown(line)}</p>`;
            }
        }
        if (inList) html += '</ul>';
        return html;
    }

    function parseInlineMarkdown(text) {
        if (!text) return '';
        let escaped = escapeHtmlText(text);
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900 dark:text-white">$1</strong>');
        escaped = escaped.replace(/\*(.*?)\*/g, '<em class="italic">$1</em>');
        escaped = escaped.replace(/`(.*?)`/g, '<code class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-mono text-xs text-sky-600 dark:text-sky-400">$1</code>');
        return escaped;
    }

    function renderCsvToTable(csv, delimiter = ',') {
        if (!csv) return '';
        const lines = csv.trim().split('\n');
        if (lines.length === 0) return '';

        let tableHtml = '<thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 uppercase font-bold sticky top-0">';
        const headers = lines[0].split(delimiter);
        tableHtml += '<tr>';
        headers.forEach(h => {
            tableHtml += `<th class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 whitespace-nowrap">${escapeHtmlText(h.trim())}</th>`;
        });
        tableHtml += '</tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-800">';

        for (let i = 1; i < lines.length; i++) {
            if (!lines[i].trim()) continue;
            const cells = lines[i].split(delimiter);
            const rowBg = i % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-900/50' : 'bg-white dark:bg-slate-900';
            tableHtml += `<tr class="${rowBg} hover:bg-sky-50/50 dark:hover:bg-sky-950/30 transition-colors">`;
            for (let j = 0; j < headers.length; j++) {
                const val = (cells[j] !== undefined) ? cells[j].trim() : '';
                tableHtml += `<td class="px-4 py-2 border border-slate-200 dark:border-slate-800 whitespace-nowrap text-slate-700 dark:text-slate-300 font-mono">${escapeHtmlText(val)}</td>`;
            }
            tableHtml += '</tr>';
        }
        tableHtml += '</tbody>';
        return tableHtml;
    }

    // Xu ly Lich nhac hen hoc tap (Study Event Reminders)
    function renderEventCardHtml(message, isMine) {
        const meta = message.metadata || {};
        const eventTitle = meta.title || message.body || 'Lịch hẹn học tập';
        const remindAtStr = meta.remind_at || '';
        const location = meta.location || '';
        const note = meta.note || '';
        const participants = meta.participants || {};
        const participantCount = Object.keys(participants).length;
        const currentUserId = {{ Auth::id() }};
        const hasJoined = !!participants[currentUserId];

        let dateBoxHtml = '';
        let isPast = false;

        if (remindAtStr) {
            try {
                const dateObj = new Date(remindAtStr);
                isPast = dateObj.getTime() < Date.now();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                const hours = String(dateObj.getHours()).padStart(2, '0');
                const mins = String(dateObj.getMinutes()).padStart(2, '0');

                dateBoxHtml = `
                    <div class="w-13 text-center shrink-0 rounded-xl overflow-hidden border ${isMine ? 'border-white/20 bg-white/10' : 'border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40'} shadow-xs">
                        <div class="bg-emerald-500 text-white text-[9px] uppercase font-bold py-0.5">
                            Thg ${month}
                        </div>
                        <div class="py-1">
                            <div class="font-black text-lg leading-none ${isMine ? 'text-white' : 'text-slate-800 dark:text-slate-100'}">
                                ${day}
                            </div>
                            <div class="text-[9px] font-semibold opacity-75 mt-0.5">
                                ${hours}:${mins}
                            </div>
                        </div>
                    </div>
                `;
            } catch(e) {}
        }

        let locationHtml = '';
        if (location) {
            const isLink = location.startsWith('http://') || location.startsWith('https://');
            const iconName = isLink ? 'video' : 'map-pin';
            const displayLoc = isLink 
                ? `<a href="${location}" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-100 font-semibold" onclick="event.stopPropagation()">Tham gia Online (Mở link)</a>`
                : `<span class="truncate">${escapeHtmlText(location)}</span>`;

            locationHtml = `
                <div class="flex items-center gap-1 text-[11px] opacity-90 truncate mb-1">
                    <i data-lucide="${iconName}" class="w-3.5 h-3.5 shrink-0"></i>
                    ${displayLoc}
                </div>
            `;
        }

        const noteHtml = note ? `<p class="text-[11px] opacity-80 italic line-clamp-2">"${escapeHtmlText(note)}"</p>` : '';

        return `
            <div id="event-card-${message.id}" class="flex flex-col gap-2.5 py-1 min-w-[260px] sm:min-w-[300px]">
                <div class="flex items-center justify-between border-b ${isMine ? 'border-white/20' : 'border-slate-200 dark:border-slate-700'} pb-2">
                    <div class="flex items-center gap-1.5 font-bold text-xs ${isMine ? 'text-white' : 'text-emerald-600 dark:text-emerald-400'}">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>LỊCH HẸN HỌC TẬP</span>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${isPast ? 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' : 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'}">
                        ${isPast ? 'Đã diễn ra' : 'Sắp tới'}
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    ${dateBoxHtml}
                    <div class="flex-1 min-w-0">
                        <h4 class="font-extrabold text-sm leading-tight ${isMine ? 'text-white' : 'text-slate-900 dark:text-white'} mb-1">
                            ${escapeHtmlText(eventTitle)}
                        </h4>
                        ${locationHtml}
                        ${noteHtml}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t ${isMine ? 'border-white/10' : 'border-slate-200 dark:border-slate-700'}">
                    <div class="text-[11px] opacity-90 flex items-center gap-1">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span id="event-count-${message.id}">${participantCount} người tham gia</span>
                    </div>
                    <button type="button" onclick="toggleJoinEvent(${message.id})" id="btn-join-event-${message.id}" class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1 shadow-xs ${hasJoined ? 'bg-emerald-500 text-white hover:bg-emerald-600' : (isMine ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-slate-200 dark:bg-slate-700 hover:bg-emerald-500 hover:text-white text-slate-700 dark:text-slate-200')}">
                        <i data-lucide="${hasJoined ? 'check' : 'user-plus'}" class="w-3.5 h-3.5"></i>
                        <span>${hasJoined ? 'Đã tham gia' : 'Tham gia'}</span>
                    </button>
                </div>
            </div>
        `;
    }

    async function submitCreateEvent(e) {
        if (e) e.preventDefault();

        const titleInput = document.getElementById('event-title');
        const remindAtInput = document.getElementById('event-remind-at');
        const locationInput = document.getElementById('event-location');
        const noteInput = document.getElementById('event-note');
        const errorEl = document.getElementById('create-event-error');
        const submitBtn = document.getElementById('btn-submit-event');

        if (!titleInput || !remindAtInput) return;

        const title = titleInput.value.trim();
        const remindAt = remindAtInput.value;

        if (!title) {
            if (errorEl) {
                errorEl.innerText = 'Vui lòng nhập tiêu đề buổi hẹn!';
                errorEl.classList.remove('hidden');
            }
            return;
        }

        if (!remindAt) {
            if (errorEl) {
                errorEl.innerText = 'Vui lòng chọn thời gian bắt đầu!';
                errorEl.classList.remove('hidden');
            }
            return;
        }

        if (errorEl) errorEl.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const remindBeforeInput = document.getElementById('event-remind-before');
            const remindBefore = remindBeforeInput ? (parseInt(remindBeforeInput.value) || 0) : 15;

            const payload = {
                title: title,
                remind_at: remindAt,
                remind_before: remindBefore,
                location: locationInput ? locationInput.value.trim() : '',
                note: noteInput ? noteInput.value.trim() : ''
            };

            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const res = await fetch(`{{ route('app.conversation.event.create', $activeConversation?->id ?? 0) }}`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                closeModal('modal-create-event');
                document.getElementById('create-event-form').reset();
                appendMessageToChat(data);
                setUpcomingReminderBanner(data);
                Toastify({
                    text: "Đã tạo lịch nhắc hẹn học tập thành công!",
                    style: { background: "#10b981" }
                }).showToast();
            } else {
                Toastify({ text: "Lỗi tạo lịch hẹn", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối máy chủ", style: { background: "#f43f5e" } }).showToast();
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    async function toggleJoinEvent(messageId) {
        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/event/${messageId}/join`, {
                method: 'POST',
                headers: headers
            });

            if (res.ok) {
                const data = await res.json();
                updateMessageInChat(data);
                const meta = data.metadata || {};
                const participants = meta.participants || {};
                const hasJoined = !!participants[{{ Auth::id() }}];
                const banner = document.getElementById('upcoming-reminder-banner');
                if (banner && parseInt(banner.getAttribute('data-event-id')) === messageId) {
                    banner.setAttribute('data-joined', hasJoined ? '1' : '0');
                }
                Toastify({
                    text: hasJoined ? "Bạn đã xác nhận tham gia buổi học!" : "Bạn đã hủy tham gia buổi học",
                    style: { background: hasJoined ? "#10b981" : "#64748b" }
                }).showToast();
            } else {
                Toastify({ text: "Lỗi tham gia lịch hẹn", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối máy chủ", style: { background: "#f43f5e" } }).showToast();
        }
    }

    // He thong Canh bao & Am thanh thong bao (Web Audio API - Khong can file mp3 ngoai)
    function playReminderChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            // Tieng ting thu 1: Not E5 (659.25Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Tieng ting thu 2: Not Ab5 (830.61Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(830.61, now + 0.15);
            gain2.gain.setValueAtTime(0.3, now + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.15);
            osc2.stop(now + 0.55);
        } catch (e) {
            console.warn('Audio chime warning:', e);
        }
    }

    // Thong bao trinh duyet (HTML5 Notification API)
    function triggerBrowserNotification(title, body) {
        if (!("Notification" in window)) return;
        if (Notification.permission === "granted") {
            try {
                new Notification(title, {
                    body: body,
                    icon: '/favicon.ico'
                });
            } catch (e) {
                console.warn('Notification warning:', e);
            }
        }
    }

    // An thanh banner nhac hen hien tai
    function dismissReminderBanner(e) {
        if (e) e.stopPropagation();
        const banner = document.getElementById('upcoming-reminder-banner');
        if (banner) {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }
    }

    // Cuon man hinh toi tin nhan lich hen
    function jumpToReminderEvent() {
        const banner = document.getElementById('upcoming-reminder-banner');
        if (!banner) return;
        const eventId = parseInt(banner.getAttribute('data-event-id'));
        if (eventId > 0) {
            jumpToSearchedMessage(eventId);
        }
    }

    // Cap nhat Banner khi nhan su kien moi hoac tao moi
    function setUpcomingReminderBanner(message) {
        if (!message || message.type !== 'event') return;
        const banner = document.getElementById('upcoming-reminder-banner');
        if (!banner) return;

        const meta = message.metadata || {};
        const newRemindAtStr = meta.remind_at;
        if (!newRemindAtStr) return;

        const newTarget = new Date(newRemindAtStr.replace(' ', 'T')).getTime();
        if (isNaN(newTarget)) return;

        const now = Date.now();
        // Neu su kien da qua thoi gian bat dau thi khong lam banner sap toi nua
        if (newTarget <= now) return;

        const currentEventId = parseInt(banner.getAttribute('data-event-id')) || 0;
        const currentRemindAtStr = banner.getAttribute('data-remind-at');
        const isHidden = banner.classList.contains('hidden');

        let shouldUpdate = false;
        if (isHidden || currentEventId === 0 || !currentRemindAtStr) {
            shouldUpdate = true;
        } else {
            const currentTarget = new Date(currentRemindAtStr.replace(' ', 'T')).getTime();
            if (currentEventId === message.id || newTarget <= currentTarget) {
                shouldUpdate = true;
            }
        }

        if (shouldUpdate) {
            banner.setAttribute('data-event-id', message.id);
            banner.setAttribute('data-remind-at', newRemindAtStr);
            banner.setAttribute('data-remind-before', meta.remind_before || 15);
            banner.setAttribute('data-location', meta.location || '');

            const participants = meta.participants || {};
            const isJoined = (message.user_id === {{ Auth::id() }}) || !!participants[{{ Auth::id() }}];
            banner.setAttribute('data-joined', isJoined ? '1' : '0');

            const titleEl = document.getElementById('reminder-banner-title');
            if (titleEl) {
                titleEl.innerText = meta.title || message.body || '';
            }

            const timeEl = document.getElementById('reminder-banner-time');
            if (timeEl) {
                timeEl.removeAttribute('data-formatted');
            }

            const joinLinkBtn = document.getElementById('btn-reminder-join-link');
            if (joinLinkBtn) {
                const loc = (meta.location || '').trim();
                const isOnline = loc.startsWith('http://') || loc.startsWith('https://');
                if (isOnline) {
                    joinLinkBtn.href = loc;
                    joinLinkBtn.classList.remove('hidden');
                    joinLinkBtn.classList.add('flex');
                } else {
                    joinLinkBtn.href = '#';
                    joinLinkBtn.classList.add('hidden');
                    joinLinkBtn.classList.remove('flex');
                }
            }

            banner.classList.remove('hidden');
            banner.classList.add('flex');
            lucide.createIcons();

            updateReminderBannerCountdown();
        }
    }

    // Bo dem nguoc thoi gian cho Banner va kich hoat Canh bao
    function updateReminderBannerCountdown() {
        const banner = document.getElementById('upcoming-reminder-banner');
        if (!banner || banner.classList.contains('hidden')) return;

        const eventId = banner.getAttribute('data-event-id');
        const remindAtStr = banner.getAttribute('data-remind-at');
        const remindBefore = parseInt(banner.getAttribute('data-remind-before')) || 15;
        const isJoined = banner.getAttribute('data-joined') === '1';
        const countdownEl = document.getElementById('reminder-banner-countdown');
        const timeEl = document.getElementById('reminder-banner-time');
        const titleEl = document.getElementById('reminder-banner-title');
        const eventTitle = titleEl ? titleEl.innerText : 'Lịch hẹn học tập';

        if (!remindAtStr || !countdownEl) return;

        const targetTime = new Date(remindAtStr.replace(' ', 'T')).getTime();
        if (isNaN(targetTime)) return;

        // Dinh dang gio:phut ngay/thang
        if (timeEl && !timeEl.getAttribute('data-formatted')) {
            const d = new Date(targetTime);
            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            timeEl.innerText = `${hours}:${mins}, ${day}/${month}`;
            timeEl.setAttribute('data-formatted', '1');
        }

        const now = Date.now();
        const diff = targetTime - now;

        if (diff > 0) {
            // Su kien sap dien ra
            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);

            let countdownText = 'Còn ';
            if (days > 0) {
                countdownText += `${days} ngày ${hours} giờ`;
            } else if (hours > 0) {
                countdownText += `${hours} giờ ${minutes} phút`;
            } else if (minutes > 0) {
                countdownText += `${minutes} phút ${seconds < 10 ? '0' : ''}${seconds}s`;
            } else {
                countdownText += `${seconds} giây`;
            }

            countdownEl.innerText = countdownText;
            countdownEl.className = 'font-bold text-amber-600 dark:text-amber-400';

            // Kiem tra nguong bao chuong truoc (chi kich hoat khi nguoi dung co tham gia)
            const thresholdMs = remindBefore * 60 * 1000;
            if (isJoined && diff <= thresholdMs) {
                const sessionKey = `alerted_event_${eventId}_${remindBefore}`;
                if (!sessionStorage.getItem(sessionKey)) {
                    sessionStorage.setItem(sessionKey, '1');
                    playReminderChime();
                    const remainMinutes = Math.max(1, Math.ceil(diff / 60000));
                    triggerBrowserNotification(
                        'Sắp đến lịch hẹn học tập!',
                        `Buổi học "${eventTitle}" sẽ bắt đầu sau ${remainMinutes} phút!`
                    );
                    Toastify({
                        text: `Sắp đến giờ học: ${eventTitle} (sau ${remainMinutes} phút)`,
                        duration: 6000,
                        style: { background: "#f59e0b" }
                    }).showToast();
                }
            }
        } else {
            // Su kien da qua thoi gian bat dau
            // Chi bao chuong neu nguoi dung dang online truc tiep trong vong 10 giay dau tien
            if (isJoined && diff <= 0 && diff > -10000) {
                const startKey = `started_event_${eventId}`;
                if (!sessionStorage.getItem(startKey)) {
                    sessionStorage.setItem(startKey, '1');
                    playReminderChime();
                    triggerBrowserNotification(
                        'Lịch học đang diễn ra!',
                        `Buổi học "${eventTitle}" đã bắt đầu. Hãy tham gia ngay!`
                    );
                    Toastify({
                        text: `Buổi học "${eventTitle}" đang diễn ra!`,
                        duration: 6000,
                        style: { background: "#10b981" }
                    }).showToast();
                }
            }

            // An thanh banner nhac hen vi su kien da qua gio bat dau
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }
    }

    // Xu ly Ghim / Bo ghim tin nhan (Pin Messages)
    async function togglePinMessage(messageId) {
        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation?->id ?? 0 }}/message/${messageId}/pin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (res.ok) {
                const data = await res.json();
                handleMessagePinnedUpdated(data.message);
                Toastify({
                    text: data.is_pinned ? "Đã ghim tin nhắn" : "Đã bỏ ghim tin nhắn",
                    duration: 2000,
                    style: { background: data.is_pinned ? "#0284c7" : "#64748b" }
                }).showToast();
            } else {
                Toastify({ text: "Lỗi ghim tin nhắn", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function unpinCurrentMessage(e) {
        if (e) e.stopPropagation();
        const bar = document.getElementById('pinned-message-bar');
        if (!bar) return;
        const pinnedId = parseInt(bar.getAttribute('data-pinned-id'));
        if (pinnedId > 0) {
            togglePinMessage(pinnedId);
        }
    }

    function jumpToPinnedMessage() {
        const bar = document.getElementById('pinned-message-bar');
        if (!bar) return;
        const pinnedId = parseInt(bar.getAttribute('data-pinned-id'));
        if (pinnedId > 0) {
            jumpToSearchedMessage(pinnedId);
        }
    }

    function handleMessagePinnedUpdated(message) {
        if (!message) return;

        // 1. Cap nhat huy hieu va nut pin tren the tin nhan (neu da co trong DOM)
        const badge = document.getElementById(`pin-badge-${message.id}`);
        const btn = document.getElementById(`btn-pin-${message.id}`);

        if (badge) {
            if (message.is_pinned) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        if (btn) {
            btn.title = message.is_pinned ? 'Bỏ ghim' : 'Ghim tin nhắn';
            const icon = btn.querySelector('svg') || btn.querySelector('i');
            if (icon) {
                if (message.is_pinned) {
                    icon.classList.add('text-amber-500', 'fill-amber-500');
                } else {
                    icon.classList.remove('text-amber-500', 'fill-amber-500');
                }
            }
        }

        // 2. Cap nhat thanh ghim duoi Header
        const bar = document.getElementById('pinned-message-bar');
        const senderNameEl = document.getElementById('pinned-sender-name');
        const previewEl = document.getElementById('pinned-message-preview');

        if (!bar || !senderNameEl || !previewEl) return;

        if (message.is_pinned) {
            // Bo ghim cac tin khac trong DOM
            document.querySelectorAll('[id^="pin-badge-"]').forEach(el => {
                if (el.id !== `pin-badge-${message.id}`) {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                }
            });
            document.querySelectorAll('[id^="btn-pin-"]').forEach(el => {
                if (el.id !== `btn-pin-${message.id}`) {
                    el.title = 'Ghim tin nhắn';
                    const icon = el.querySelector('svg') || el.querySelector('i');
                    if (icon) icon.classList.remove('text-amber-500', 'fill-amber-500');
                }
            });

            bar.setAttribute('data-pinned-id', message.id);
            senderNameEl.innerText = (message.user ? message.user.name : '');

            let previewText = message.body || '';
            if (message.type === 'image') previewText = '[Hình ảnh]' + (message.body ? ': ' + message.body : '');
            else if (message.type === 'audio') previewText = '[Tin nhắn thoại]';
            else if (message.type === 'quiz') previewText = '[Bài kiểm tra]: ' + (message.body || '');
            else if (message.type === 'game_dice') previewText = '[Tung xúc xắc]';
            else if (message.type === 'game_rps') previewText = '[Oẳn tù tì]';
            else if (message.type === 'event') previewText = '[Lịch hẹn]: ' + (message.body || '');
            else if (message.type === 'document') previewText = '[Tài liệu]: ' + ((message.metadata && message.metadata.file_name) || message.body || '');

            previewEl.innerText = previewText;
            bar.classList.remove('hidden');
            bar.classList.add('flex');
        } else {
            const currentPinnedId = parseInt(bar.getAttribute('data-pinned-id'));
            if (currentPinnedId === message.id) {
                bar.setAttribute('data-pinned-id', '0');
                bar.classList.add('hidden');
                bar.classList.remove('flex');
            }
        }

        lucide.createIcons();
    }

    // ===== CHUC NANG CHUYEN TIEP & GO TIN NHAN =====
    let currentForwardMessageId = null;
    let selectedForwardConvIds = [];

    function openForwardModal(messageId) {
        currentForwardMessageId = messageId;
        selectedForwardConvIds = [];

        const msgEl = document.getElementById('msg-' + messageId);
        let previewSender = 'Tin nhắn';
        let previewText = 'Nội dung tin nhắn';
        let iconName = 'message-square';

        if (msgEl) {
            const senderEl = msgEl.querySelector('.font-bold.text-slate-700, .font-bold.text-slate-300');
            if (senderEl) {
                previewSender = senderEl.innerText.trim();
            } else {
                previewSender = 'Bạn';
            }

            if (msgEl.querySelector('audio')) {
                iconName = 'mic';
                previewText = '[Tin nhắn thoại]';
            } else if (msgEl.querySelector('img')) {
                iconName = 'image';
                previewText = '[Hình ảnh]';
            } else if (msgEl.querySelector('[data-lucide="file-text"], [data-lucide="file"], [data-lucide="file-spreadsheet"]')) {
                iconName = 'file-text';
                const docNameEl = msgEl.querySelector('.font-bold.text-xs.truncate');
                previewText = docNameEl ? `[Tài liệu]: ${docNameEl.innerText.trim()}` : '[Tài liệu]';
            } else if (msgEl.querySelector('[data-lucide="help-circle"]')) {
                iconName = 'help-circle';
                previewText = '[Bài kiểm tra]';
            } else if (msgEl.querySelector('[data-lucide="box"]')) {
                iconName = 'box';
                previewText = '[Tung xúc xắc]';
            } else if (msgEl.querySelector('[data-lucide="swords"]')) {
                iconName = 'swords';
                previewText = '[Thách đấu Oẳn tù tì]';
            } else if (msgEl.querySelector('[data-lucide="calendar"]')) {
                iconName = 'calendar';
                previewText = '[Lịch hẹn học tập]';
            } else {
                const bubbleEl = msgEl.querySelector('.max-w-md');
                if (bubbleEl) {
                    const textClone = bubbleEl.cloneNode(true);
                    textClone.querySelectorAll('.group-hover\\:opacity-100, .reaction-picker-wrap, [id^="reactions-bar-"], [id^="pin-badge-"]').forEach(n => n.remove());
                    previewText = textClone.innerText.trim();
                }
            }
        }

        const senderBox = document.getElementById('forward-preview-sender');
        const textBox = document.getElementById('forward-preview-text');
        const iconBox = document.getElementById('forward-preview-icon');
        const searchInput = document.getElementById('forward-search-input');
        const errorEl = document.getElementById('forward-message-error');

        if (senderBox) senderBox.innerText = previewSender;
        if (textBox) textBox.innerText = previewText ? previewText.substring(0, 100) : 'Nội dung tin nhắn';
        if (iconBox) {
            iconBox.innerHTML = `<i data-lucide="${iconName}" class="w-4 h-4"></i>`;
        }
        if (searchInput) searchInput.value = '';
        if (errorEl) {
            errorEl.innerText = '';
            errorEl.classList.add('hidden');
        }

        document.querySelectorAll('.forward-checkbox').forEach(chk => {
            chk.checked = false;
        });
        document.querySelectorAll('.forward-conv-item').forEach(item => {
            item.classList.remove('hidden', 'bg-indigo-50', 'dark:bg-indigo-950/30', 'border-indigo-300', 'dark:border-indigo-700');
        });

        updateForwardSelectedUI();
        openModal('modal-forward-message');
        lucide.createIcons();
    }

    function closeForwardModal() {
        closeModal('modal-forward-message');
        currentForwardMessageId = null;
        selectedForwardConvIds = [];
    }

    function filterForwardConversations() {
        const input = document.getElementById('forward-search-input');
        const q = input ? input.value.toLowerCase().trim() : '';
        document.querySelectorAll('.forward-conv-item').forEach(item => {
            const name = item.getAttribute('data-conv-name') || '';
            if (!q || name.includes(q)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }

    function toggleForwardConv(convId) {
        const chk = document.getElementById('forward-chk-' + convId);
        const item = document.querySelector(`.forward-conv-item[data-conv-id="${convId}"]`);
        if (!chk) return;

        const idx = selectedForwardConvIds.indexOf(convId);
        if (idx > -1) {
            selectedForwardConvIds.splice(idx, 1);
            chk.checked = false;
            if (item) item.classList.remove('bg-indigo-50', 'dark:bg-indigo-950/30', 'border-indigo-300', 'dark:border-indigo-700');
        } else {
            selectedForwardConvIds.push(convId);
            chk.checked = true;
            if (item) item.classList.add('bg-indigo-50', 'dark:bg-indigo-950/30', 'border-indigo-300', 'dark:border-indigo-700');
        }

        updateForwardSelectedUI();
    }

    function updateForwardSelectedUI() {
        const count = selectedForwardConvIds.length;
        const countEl = document.getElementById('forward-selected-count');
        const btn = document.getElementById('btn-submit-forward');
        const btnText = document.getElementById('forward-btn-text');

        if (countEl) {
            countEl.innerText = `Đã chọn ${count} cuộc trò chuyện`;
        }
        if (btnText) {
            btnText.innerText = count > 0 ? `Gửi (${count})` : 'Gửi';
        }
        if (btn) {
            btn.disabled = count === 0;
        }
    }

    async function submitForwardMessage() {
        if (!currentForwardMessageId || selectedForwardConvIds.length === 0) return;

        const btn = document.getElementById('btn-submit-forward');
        const errorEl = document.getElementById('forward-message-error');
        const originalHtml = btn ? btn.innerHTML : '';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> <span>Đang gửi...</span>';
            lucide.createIcons();
        }
        if (errorEl) {
            errorEl.innerText = '';
            errorEl.classList.add('hidden');
        }

        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const currentConvId = {{ $activeConversation?->id ?? 0 }};
            const res = await fetch(`{{ url('app/conversation') }}/${currentConvId}/message/${currentForwardMessageId}/forward`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    target_conversation_ids: selectedForwardConvIds
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                if (data.messages && Array.isArray(data.messages)) {
                    data.messages.forEach(msg => {
                        if (msg.conversation_id === currentConvId) {
                            appendMessageToChat(msg);
                        }
                    });
                }
                closeForwardModal();
                Toastify({
                    text: `Đã chuyển tiếp tin nhắn đến ${data.forwarded_count} cuộc trò chuyện!`,
                    duration: 3000,
                    style: { background: "#6366f1" }
                }).showToast();
            } else {
                const errMsg = data.error || 'Có lỗi xảy ra khi chuyển tiếp tin nhắn';
                if (errorEl) {
                    errorEl.innerText = errMsg;
                    errorEl.classList.remove('hidden');
                } else {
                    Toastify({ text: errMsg, style: { background: "#f43f5e" } }).showToast();
                }
            }
        } catch (err) {
            console.error('Loi forward message:', err);
            if (errorEl) {
                errorEl.innerText = 'Lỗi kết nối mạng';
                errorEl.classList.remove('hidden');
            } else {
                Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            }
        } finally {
            if (btn) {
                btn.disabled = selectedForwardConvIds.length === 0;
                btn.innerHTML = originalHtml;
                lucide.createIcons();
            }
        }
    }

    async function confirmUnsendMessage(messageId) {
        if (!confirm("Bạn có chắc chắn muốn thu hồi tin nhắn này đối với tất cả mọi người? Sau khi thu hồi, nội dung sẽ không thể phục hồi.")) {
            return;
        }

        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }

            const currentConvId = {{ $activeConversation?->id ?? 0 }};
            const res = await fetch(`{{ url('app/conversation') }}/${currentConvId}/message/${messageId}/unsend`, {
                method: 'POST',
                headers: headers
            });

            const data = await res.json();
            if (res.ok && data.success) {
                updateMessageInChat(data.message);
                handleMessagePinnedUpdated(data.message);
                Toastify({
                    text: "Đã thu hồi tin nhắn thành công!",
                    duration: 2500,
                    style: { background: "#10b981" }
                }).showToast();
            } else {
                Toastify({
                    text: data.error || "Không thể thu hồi tin nhắn",
                    duration: 3000,
                    style: { background: "#f43f5e" }
                }).showToast();
            }
        } catch (err) {
            console.error('Loi unsend message:', err);
            Toastify({
                text: "Lỗi kết nối khi thu hồi tin nhắn",
                duration: 3000,
                style: { background: "#f43f5e" }
            }).showToast();
        }
    }

    // =========================================================
    // CAI DAT CUOC TRO CHUYEN: GHIM, MUTE, BIET DANH
    // =========================================================
    let isCurrentConversationMuted = {{ ($currentParticipant && $currentParticipant->isMuted()) ? 'true' : 'false' }};

    function playIncomingMessageSound() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.exponentialRampToValueAtTime(1318.51, now + 0.12);
            gain.gain.setValueAtTime(0.08, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.25);
        } catch (e) {
            // Trinh duyet chan autoplay audio
        }
    }

    async function handleTogglePinChat() {
        const convId = {{ $activeConversation?->id ?? 0 }};
        if (!convId) return;

        const pinBtn = document.getElementById('btn-header-pin-chat');
        const headerMain = document.getElementById('chat-header-main');

        try {
            const res = await fetch(`{{ url('app/conversation') }}/${convId}/pin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            });

            const data = await res.json();
            if (data.success) {
                const isPinned = !!data.is_pinned;
                if (headerMain) {
                    headerMain.setAttribute('data-is-pinned', isPinned ? '1' : '0');
                }

                if (pinBtn) {
                    pinBtn.title = isPinned ? 'Bỏ ghim cuộc trò chuyện' : 'Ghim cuộc trò chuyện lên đầu';
                    if (isPinned) {
                        pinBtn.className = 'p-2 text-amber-500 bg-amber-50 dark:bg-amber-950/30 rounded-xl transition-all';
                        pinBtn.innerHTML = '<i data-lucide="pin" class="w-5 h-5 fill-amber-500"></i>';
                    } else {
                        pinBtn.className = 'p-2 text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all';
                        pinBtn.innerHTML = '<i data-lucide="pin" class="w-5 h-5"></i>';
                    }
                }

                updateSidebarPinIcon(convId, isPinned);

                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }

                Toastify({
                    text: data.message,
                    duration: 3000,
                    style: { background: isPinned ? "#f59e0b" : "#64748b" }
                }).showToast();
            } else {
                Toastify({ text: data.message || "Lỗi cập nhật ghim", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            console.error('Loi toggle pin chat:', err);
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function updateSidebarPinIcon(convId, isPinned) {
        const sidebarLink = document.querySelector(`a[href*="/app/chat-board/${convId}"]`);
        if (!sidebarLink) return;
        const iconContainer = sidebarLink.querySelector('.shrink-0.flex.items-center.gap-1') || sidebarLink.querySelector('.flex.items-center.gap-1.shrink-0');
        if (!iconContainer) return;

        let pinIcon = iconContainer.querySelector('i[data-lucide="pin"]');
        if (isPinned) {
            if (!pinIcon) {
                iconContainer.insertAdjacentHTML('beforeend', '<i data-lucide="pin" class="w-3.5 h-3.5 text-amber-500 fill-amber-500" title="Đã ghim"></i>');
            }
        } else {
            if (pinIcon) {
                pinIcon.remove();
            }
        }
    }

    function toggleMuteDropdown(e) {
        if (e) e.stopPropagation();
        const dropdown = document.getElementById('header-mute-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    async function handleSelectMuteDuration(duration) {
        const dropdown = document.getElementById('header-mute-dropdown');
        if (dropdown) dropdown.classList.add('hidden');

        const convId = {{ $activeConversation?->id ?? 0 }};
        if (!convId) return;

        try {
            const res = await fetch(`{{ url('app/conversation') }}/${convId}/mute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ duration: duration })
            });

            const data = await res.json();
            if (data.success) {
                isCurrentConversationMuted = !!data.is_muted;
                const muteBtn = document.getElementById('btn-header-mute-chat');
                const headerMain = document.getElementById('chat-header-main');
                const unmuteOptionWrap = document.getElementById('mute-unmute-option-wrap');

                if (headerMain) {
                    headerMain.setAttribute('data-is-muted', isCurrentConversationMuted ? '1' : '0');
                }

                if (unmuteOptionWrap) {
                    if (isCurrentConversationMuted) {
                        unmuteOptionWrap.classList.remove('hidden');
                    } else {
                        unmuteOptionWrap.classList.add('hidden');
                    }
                }

                if (muteBtn) {
                    if (isCurrentConversationMuted) {
                        muteBtn.className = 'p-2 text-rose-500 bg-rose-50 dark:bg-rose-950/30 rounded-xl transition-all';
                        muteBtn.title = 'Đang tắt thông báo (Nhấn để tùy chỉnh)';
                        muteBtn.innerHTML = '<i data-lucide="bell-off" class="w-5 h-5"></i>';
                    } else {
                        muteBtn.className = 'p-2 text-slate-400 hover:text-sky-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all';
                        muteBtn.title = 'Tắt thông báo cuộc trò chuyện';
                        muteBtn.innerHTML = '<i data-lucide="bell" class="w-5 h-5"></i>';
                    }
                }

                updateSidebarMuteIcon(convId, isCurrentConversationMuted);

                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }

                Toastify({
                    text: data.message,
                    duration: 3500,
                    style: { background: isCurrentConversationMuted ? "#f43f5e" : "#10b981" }
                }).showToast();
            } else {
                Toastify({ text: data.message || "Lỗi cập nhật thông báo", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            console.error('Loi mute conversation:', err);
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function updateSidebarMuteIcon(convId, isMuted) {
        const sidebarLink = document.querySelector(`a[href*="/app/chat-board/${convId}"]`);
        if (!sidebarLink) return;
        const iconContainer = sidebarLink.querySelector('.shrink-0.flex.items-center.gap-1') || sidebarLink.querySelector('.flex.items-center.gap-1.shrink-0');
        if (!iconContainer) return;

        let muteIcon = iconContainer.querySelector('i[data-lucide="bell-off"]');
        if (isMuted) {
            if (!muteIcon) {
                iconContainer.insertAdjacentHTML('afterbegin', '<i data-lucide="bell-off" class="w-3.5 h-3.5 text-slate-400" title="Đang tắt thông báo"></i>');
            }
        } else {
            if (muteIcon) {
                muteIcon.remove();
            }
        }
    }

    function openChangeNicknameModal() {
        const headerMain = document.getElementById('chat-header-main');
        const currentNickname = headerMain ? (headerMain.getAttribute('data-nickname') || '') : '';
        const input = document.getElementById('input-custom-nickname');
        const btnRemove = document.getElementById('btn-remove-nickname');

        if (input) {
            input.value = currentNickname;
        }

        if (btnRemove) {
            if (currentNickname.trim().length > 0) {
                btnRemove.classList.remove('hidden');
            } else {
                btnRemove.classList.add('hidden');
            }
        }

        openModal('modal-change-nickname');
        if (input) {
            setTimeout(() => input.focus(), 100);
        }
    }

    async function handleSaveNickname(isRemove = false) {
        const convId = {{ $activeConversation?->id ?? 0 }};
        if (!convId) return;

        const input = document.getElementById('input-custom-nickname');
        const newNickname = isRemove ? '' : (input ? input.value.trim() : '');

        try {
            const res = await fetch(`{{ url('app/conversation') }}/${convId}/nickname`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nickname: newNickname })
            });

            const data = await res.json();
            if (data.success) {
                closeModal('modal-change-nickname');

                const savedNickname = data.nickname || '';
                const headerMain = document.getElementById('chat-header-main');
                const originalName = headerMain ? (headerMain.getAttribute('data-original-name') || '') : '';

                if (headerMain) {
                    headerMain.setAttribute('data-nickname', savedNickname);
                }

                const nameTextEl = document.getElementById('chat-header-name-text');
                const originalBadgeEl = document.getElementById('chat-header-original-badge');

                if (nameTextEl) {
                    nameTextEl.innerText = savedNickname ? savedNickname : originalName;
                }

                if (originalBadgeEl) {
                    if (savedNickname && savedNickname !== originalName) {
                        originalBadgeEl.innerText = `(${originalName})`;
                        originalBadgeEl.classList.remove('hidden');
                    } else {
                        originalBadgeEl.classList.add('hidden');
                    }
                }

                updateSidebarChatName(convId, savedNickname ? savedNickname : originalName);

                Toastify({
                    text: data.message,
                    duration: 3000,
                    style: { background: "#6366f1" }
                }).showToast();
            } else {
                Toastify({ text: data.message || "Lỗi lưu biệt danh", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            console.error('Loi save nickname:', err);
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function updateSidebarChatName(convId, displayName) {
        const sidebarLink = document.querySelector(`a[href*="/app/chat-board/${convId}"]`);
        if (!sidebarLink) return;
        const nameHeader = sidebarLink.querySelector('h3');
        if (nameHeader) {
            nameHeader.innerText = displayName;
        }
    }

    // Event Listeners (Echo Realtime & Click Outside)
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('chat-messages-container');
        if (container) {
            hasMoreOlderMessages = container.getAttribute('data-has-more') === '1';
            oldestMessageId = parseInt(container.getAttribute('data-oldest-id')) || 0;
        }

        smartScrollToBottom(true, false);

        // Khoi tao quyen thong bao trinh duyet neu chua hoi
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }

        // Khoi chay bo dem nguoc thoi gian cho lich hen
        updateReminderBannerCountdown();
        setInterval(updateReminderBannerCountdown, 1000);

        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('conversation.{{ $activeConversation?->id ?? 0 }}')
                .listen('.MessageSent', (e) => {
                    hideTypingIndicator();
                    appendMessageToChat(e.message);
                    if (e.message.type === 'event') {
                        setUpcomingReminderBanner(e.message);
                    }
                    if (e.message.user_id !== {{ Auth::id() }} && !isCurrentConversationMuted) {
                        playIncomingMessageSound();
                        if (document.hidden) {
                            triggerBrowserNotification(
                                e.message.user ? e.message.user.name : 'Tin nhắn mới',
                                e.message.body || 'Bạn có một tin nhắn mới'
                            );
                        }
                    }
                })
                .listen('.MessageUpdated', (e) => {
                    updateMessageInChat(e.message);
                    handleMessagePinnedUpdated(e.message);
                    if (e.message.type === 'event') {
                        setUpcomingReminderBanner(e.message);
                    }
                })
                .listenForWhisper('typing', (e) => {
                    if (e.user_id !== {{ Auth::id() }}) {
                        showTypingIndicator(e.user_name);
                    }
                });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.reaction-picker-wrap')) {
                document.querySelectorAll('.reaction-popup').forEach(el => {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                });
            }
            if (!e.target.closest('#chat-search-panel') && !e.target.closest('button[onclick*="toggleChatSearch"]')) {
                const searchPanel = document.getElementById('chat-search-panel');
                if (searchPanel && !searchPanel.classList.contains('hidden')) {
                    closeChatSearch();
                }
            }
            if (!e.target.closest('#header-mute-container')) {
                const muteDropdown = document.getElementById('header-mute-dropdown');
                if (muteDropdown && !muteDropdown.classList.contains('hidden')) {
                    muteDropdown.classList.add('hidden');
                }
            }
        });
    });
@endif
</script>