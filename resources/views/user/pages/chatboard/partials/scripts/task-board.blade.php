<script>
// =========================================================
// BANG PHAN CONG CONG VIEC & NHIEM VU NHOM (TASK BOARD)
// =========================================================

let taskBoardCurrentMembers = [];

function openTaskBoardModal() {
    openModal('modal-task-board');
    toggleCreateTaskForm(false);
    loadTaskBoardData();
}

function toggleCreateTaskForm(forceState = null) {
    const wrap = document.getElementById('task-create-form-wrap');
    const btn = document.getElementById('btn-toggle-create-task');
    if (!wrap) return;

    const willShow = forceState !== null ? forceState : wrap.classList.contains('hidden');
    if (willShow) {
        wrap.classList.remove('hidden');
        if (btn) {
            btn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i><span>Hủy tạo việc</span>';
        }
        const titleInput = document.getElementById('task-input-title');
        if (titleInput) {
            setTimeout(() => titleInput.focus(), 100);
        }
    } else {
        wrap.classList.add('hidden');
        if (btn) {
            btn.innerHTML = '<i data-lucide="plus" class="w-4 h-4"></i><span>Giao việc mới</span>';
        }
        const form = document.getElementById('form-create-task');
        if (form) form.reset();
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}

async function loadTaskBoardData() {
    const convId = {{ $activeConversation?->id ?? 0 }};
    if (!convId) return;

    const loadingEl = document.getElementById('task-board-loading');
    const columnsEl = document.getElementById('task-board-columns');

    if (loadingEl) loadingEl.classList.remove('hidden');
    if (columnsEl) columnsEl.classList.add('hidden');

    try {
        const res = await fetch(`{{ url('app/conversation') }}/${convId}/tasks`, {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) throw new Error('Không thể tải danh sách công việc');

        const data = await res.json();
        taskBoardCurrentMembers = data.members || [];

        // 1. Cap nhat dropdown danh sach thanh vien
        populateTaskAssigneeSelect(taskBoardCurrentMembers);

        // 2. Cap nhat so dem
        const counts = data.counts || { total: 0, todo: 0, in_progress: 0, done: 0 };
        document.getElementById('task-count-total').innerText = counts.total || 0;
        document.getElementById('task-count-todo').innerText = counts.todo || 0;
        document.getElementById('task-count-progress').innerText = counts.in_progress || 0;
        document.getElementById('task-count-done').innerText = counts.done || 0;

        document.getElementById('col-count-todo').innerText = counts.todo || 0;
        document.getElementById('col-count-in_progress').innerText = counts.in_progress || 0;
        document.getElementById('col-count-done').innerText = counts.done || 0;

        // 3. Render cac the vao 3 cot
        renderTaskColumns(data.tasks || []);

        if (loadingEl) loadingEl.classList.add('hidden');
        if (columnsEl) columnsEl.classList.remove('hidden');

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    } catch (err) {
        console.error('Loi tai task board:', err);
        if (loadingEl) {
            loadingEl.innerHTML = '<p class="text-xs text-rose-500 py-4">Lỗi kết nối khi tải bảng công việc.</p>';
        }
    }
}

function populateTaskAssigneeSelect(members) {
    const select = document.getElementById('task-select-assignee');
    if (!select) return;

    select.innerHTML = '<option value="">-- Chưa giao ai (Việc chung nhóm) --</option>';
    members.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.innerText = m.name + (m.role === 'admin' || m.role === 'owner' ? ' (Trưởng nhóm)' : '');
        select.appendChild(opt);
    });
}

function renderTaskColumns(tasks) {
    const cTodo = document.getElementById('tasks-container-todo');
    const cProgress = document.getElementById('tasks-container-in_progress');
    const cDone = document.getElementById('tasks-container-done');

    if (!cTodo || !cProgress || !cDone) return;

    cTodo.innerHTML = '';
    cProgress.innerHTML = '';
    cDone.innerHTML = '';

    const emptyHtml = (text) => `
        <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-center p-3 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
            <i data-lucide="clipboard-list" class="w-5 h-5 mb-1 opacity-50"></i>
            <span class="text-[11px]">${text}</span>
        </div>
    `;

    const tasksTodo = tasks.filter(t => t.status === 'todo');
    const tasksProgress = tasks.filter(t => t.status === 'in_progress');
    const tasksDone = tasks.filter(t => t.status === 'done');

    if (tasksTodo.length === 0) cTodo.innerHTML = emptyHtml('Chưa có việc cần làm');
    else tasksTodo.forEach(t => cTodo.insertAdjacentHTML('beforeend', buildTaskCardHtml(t)));

    if (tasksProgress.length === 0) cProgress.innerHTML = emptyHtml('Chưa có việc đang làm');
    else tasksProgress.forEach(t => cProgress.insertAdjacentHTML('beforeend', buildTaskCardHtml(t)));

    if (tasksDone.length === 0) cDone.innerHTML = emptyHtml('Chưa có việc hoàn thành');
    else tasksDone.forEach(t => cDone.insertAdjacentHTML('beforeend', buildTaskCardHtml(t)));
}

function buildTaskCardHtml(task) {
    const priorityLabels = {
        'low': { text: 'Thấp', class: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' },
        'medium': { text: 'Vừa', class: 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800' },
        'high': { text: 'Cao', class: 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800' },
        'urgent': { text: 'Khẩn cấp', class: 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800' },
    };

    const pInfo = priorityLabels[task.priority] || priorityLabels['medium'];

    // Xu ly han chot (due date)
    let dueDateHtml = '';
    if (task.due_date) {
        const d = new Date(task.due_date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const isPast = d.getTime() < Date.now() && task.status !== 'done';

        dueDateHtml = `
            <div class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-md ${isPast ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'}">
                <i data-lucide="${isPast ? 'alert-circle' : 'calendar'}" class="w-3 h-3"></i>
                <span>${isPast ? 'Quá hạn: ' : 'Hạn: '}${day}/${month}</span>
            </div>
        `;
    }

    // Xu ly nguoi duoc giao
    let assigneeHtml = '';
    if (task.assignee) {
        const initial = task.assignee.name ? task.assignee.name.charAt(0).toUpperCase() : 'U';
        assigneeHtml = `
            <div class="flex items-center gap-1.5" title="Người nhận: ${task.assignee.name}">
                <div class="w-5 h-5 rounded-full overflow-hidden bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 font-bold text-[9px] flex items-center justify-center shrink-0">
                    ${task.assignee.avatar_url ? `<img src="${task.assignee.avatar_url}" alt="${task.assignee.name}" class="w-full h-full object-cover">` : initial}
                </div>
                <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 truncate max-w-[100px]">${task.assignee.name}</span>
            </div>
        `;
    } else {
        assigneeHtml = `
            <span class="text-[10px] text-slate-400 italic">Chưa giao</span>
        `;
    }

    // Nut chuyen trang thai
    let actionButtons = '';
    if (task.status === 'todo') {
        actionButtons = `
            <button type="button" onclick="handleUpdateTaskStatus(${task.id}, 'in_progress')" class="px-2 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 dark:hover:bg-sky-900/60 text-sky-600 dark:text-sky-400 text-[10px] font-bold transition-colors flex items-center gap-1" title="Bắt đầu thực hiện">
                <i data-lucide="play" class="w-3 h-3"></i>
                <span>Bắt đầu</span>
            </button>
            <button type="button" onclick="handleUpdateTaskStatus(${task.id}, 'done')" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold transition-colors flex items-center gap-1" title="Đánh dấu hoàn thành">
                <i data-lucide="check" class="w-3 h-3"></i>
                <span>Xong</span>
            </button>
        `;
    } else if (task.status === 'in_progress') {
        actionButtons = `
            <button type="button" onclick="handleUpdateTaskStatus(${task.id}, 'todo')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" title="Chuyển về Cần làm">
                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
            </button>
            <button type="button" onclick="handleUpdateTaskStatus(${task.id}, 'done')" class="px-2 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-bold transition-colors flex items-center gap-1 shadow-xs" title="Hoàn thành công việc">
                <i data-lucide="check" class="w-3 h-3"></i>
                <span>Hoàn thành</span>
            </button>
        `;
    } else if (task.status === 'done') {
        actionButtons = `
            <button type="button" onclick="handleUpdateTaskStatus(${task.id}, 'in_progress')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-semibold transition-colors flex items-center gap-1" title="Làm lại nhiệm vụ">
                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                <span>Làm lại</span>
            </button>
        `;
    }

    return `
        <div id="task-card-${task.id}" class="p-3 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-xl shadow-xs hover:shadow-md transition-all space-y-2 group">
            <div class="flex items-start justify-between gap-1.5">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold border ${pInfo.class}">
                    ${pInfo.text}
                </span>
                <button type="button" onclick="handleDeleteTask(${task.id})" class="opacity-0 group-hover:opacity-100 p-1 text-slate-300 hover:text-rose-500 rounded transition-all" title="Xóa công việc">
                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                </button>
            </div>

            <div>
                <h5 class="font-bold text-xs text-slate-800 dark:text-slate-100 leading-snug ${task.status === 'done' ? 'line-through opacity-70' : ''}">
                    ${escapeHtml(task.title)}
                </h5>
                ${task.description ? `
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                        ${escapeHtml(task.description)}
                    </p>
                ` : ''}
            </div>

            <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-700/60">
                ${assigneeHtml}
                ${dueDateHtml}
            </div>

            <div class="flex items-center justify-end gap-1.5 pt-0.5">
                ${actionButtons}
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

async function handleCreateTask(e) {
    e.preventDefault();
    const convId = {{ $activeConversation?->id ?? 0 }};
    if (!convId) return;

    const title = document.getElementById('task-input-title').value.trim();
    const assigneeId = document.getElementById('task-select-assignee').value;
    const dueDate = document.getElementById('task-input-due-date').value;
    const priority = document.getElementById('task-select-priority').value;
    const description = document.getElementById('task-input-description').value.trim();

    if (!title) return;

    const btnSubmit = document.getElementById('btn-submit-task');
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerText = 'Đang lưu...';
    }

    try {
        const res = await fetch(`{{ url('app/conversation') }}/${convId}/tasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                title: title,
                assignee_id: assigneeId || null,
                due_date: dueDate || null,
                priority: priority,
                description: description || null,
            })
        });

        const data = await res.json();
        if (data.success) {
            toggleCreateTaskForm(false);
            loadTaskBoardData();

            Toastify({
                text: data.message,
                duration: 3000,
                style: { background: "#6366f1" }
            }).showToast();
        } else {
            Toastify({ text: data.message || "Lỗi tạo công việc", style: { background: "#f43f5e" } }).showToast();
        }
    } catch (err) {
        console.error('Loi create task:', err);
        Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerText = 'Tạo công việc';
        }
    }
}

async function handleUpdateTaskStatus(taskId, newStatus) {
    const convId = {{ $activeConversation?->id ?? 0 }};
    if (!convId) return;

    try {
        const res = await fetch(`{{ url('app/conversation') }}/${convId}/tasks/${taskId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus })
        });

        const data = await res.json();
        if (data.success) {
            loadTaskBoardData();
            Toastify({
                text: data.message,
                duration: 2500,
                style: { background: newStatus === 'done' ? "#10b981" : "#0284c7" }
            }).showToast();
        } else {
            Toastify({ text: data.message || "Lỗi cập nhật", style: { background: "#f43f5e" } }).showToast();
        }
    } catch (err) {
        console.error('Loi update status:', err);
        Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
    }
}

async function handleDeleteTask(taskId) {
    if (!confirm('Bạn có chắc chắn muốn xóa công việc này khỏi bảng nhóm?')) {
        return;
    }

    const convId = {{ $activeConversation?->id ?? 0 }};
    if (!convId) return;

    try {
        const res = await fetch(`{{ url('app/conversation') }}/${convId}/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });

        const data = await res.json();
        if (data.success) {
            loadTaskBoardData();
            Toastify({
                text: data.message,
                duration: 2500,
                style: { background: "#64748b" }
            }).showToast();
        } else {
            Toastify({ text: data.message || "Lỗi xóa công việc", style: { background: "#f43f5e" } }).showToast();
        }
    } catch (err) {
        console.error('Loi delete task:', err);
        Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
    }
}
</script>
