<!-- MODAL: TAO NHOM HOC TAP -->
<div id="modal-create-group" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden transition-opacity">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Tao Nhom Hoc Tap</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ket noi ban be de trao doi va chia se bai tap</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-create-group')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="space-y-4">
            <!-- 1. Ten nhom hoc tap -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Ten nhom hoc tap <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <i data-lucide="message-square" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="group-name-input" maxlength="100" 
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors dark:text-white" 
                           placeholder="VD: Nhom on thi Lap trinh Web">
                </div>
            </div>

            <!-- 2. Khay thanh vien da chon (Selected Chips Tray) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Thanh vien da chon
                    </label>
                    <span id="group-selected-count-badge" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                        0 thanh vien (Toi thieu 2)
                    </span>
                </div>
                
                <div id="group-selected-chips-container" class="min-h-[46px] max-h-24 overflow-y-auto flex flex-wrap gap-1.5 p-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl items-center">
                    <span id="group-selected-empty-text" class="text-xs text-slate-400 italic px-1 select-none">
                        Chua chon thanh vien nao...
                    </span>
                </div>
            </div>

            <!-- 3. Thanh loc tim kiem ban be -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Chon ban be vao nhom
                </label>
                <div class="relative mb-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="group-filter-input" oninput="filterGroupFriendsList(this.value)"
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2 text-xs focus:outline-none focus:border-indigo-500 transition-colors dark:text-white" 
                           placeholder="Tim kiem ban be theo ten hoac email...">
                </div>

                <!-- Danh sach ban be -->
                <div id="group-friends-list-container" class="max-h-44 overflow-y-auto space-y-1 border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-slate-50/50 dark:bg-slate-900/30">
                    @if(isset($friends) && $friends->count() > 0)
                        @foreach($friends as $friend)
                            @php
                                $firstLetter = strtoupper(substr($friend->name, 0, 1));
                            @endphp
                            <div class="group-friend-row flex items-center justify-between p-2 hover:bg-white dark:hover:bg-slate-800 rounded-xl cursor-pointer transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700 select-none"
                                 data-id="{{ $friend->id }}"
                                 data-name="{{ $friend->name }}"
                                 data-email="{{ $friend->email }}"
                                 onclick="toggleGroupMemberSelection({{ $friend->id }}, '{{ addslashes($friend->name) }}', '{{ $friend->avatar ?? '' }}')">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ $firstLetter }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $friend->name }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $friend->email }}</p>
                                    </div>
                                </div>
                                <input type="checkbox" id="group-checkbox-{{ $friend->id }}" 
                                       class="group-member-checkbox w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 pointer-events-none"
                                       value="{{ $friend->id }}">
                            </div>
                        @endforeach
                        <p id="group-friends-search-empty" class="hidden text-xs text-slate-400 text-center py-4">
                            Khong tim thay ban be phu hop voi tu khoa.
                        </p>
                    @else
                        <div class="text-center py-5 px-3">
                            <p class="text-xs text-slate-400">Ban chua co ban be nao trong he thong.</p>
                            <p class="text-[11px] text-slate-500 mt-1">Hay ket ban bang email truoc khi tao nhom hoc tap.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeModal('modal-create-group')" 
                        class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-semibold text-xs transition-colors">
                    Huy bo
                </button>
                <button type="button" id="btn-create-group-submit" onclick="submitCreateGroup()" 
                        class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span id="btn-create-group-text">Tao Nhom</span>
                </button>
            </div>

            <p id="create-group-msg" class="text-xs text-center hidden"></p>
        </div>
    </div>
</div>
