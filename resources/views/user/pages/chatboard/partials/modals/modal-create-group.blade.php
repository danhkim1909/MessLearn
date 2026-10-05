<!-- MODAL 2: TẠO NHÓM MỚI -->
<div id="modal-create-group" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Tạo Nhóm Học Tập</h3>
            <button type="button" onclick="closeModal('modal-create-group')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tên nhóm</label>
            <input type="text" id="group-name" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors dark:text-white mb-4" placeholder="VD: Nhóm ôn thi Toán">
            
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Chọn bạn bè vào nhóm</label>
            <div class="max-h-40 overflow-y-auto space-y-2 mb-4 border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50">
                @if(isset($friends) && $friends->count() > 0)
                    @foreach($friends as $friend)
                        <label class="flex items-center gap-3 p-2 hover:bg-white dark:hover:bg-slate-800 rounded-lg cursor-pointer transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                            <input type="checkbox" name="group_members[]" value="{{ $friend->id }}" class="w-4 h-4 text-indigo-500 border-slate-300 rounded focus:ring-indigo-500">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($friend->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $friend->name }}</span>
                        </label>
                    @endforeach
                @else
                    <p class="text-xs text-slate-400 text-center py-2">Bạn chưa kết bạn với ai.</p>
                @endif
            </div>

            <button type="button" onclick="createGroup()" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 transition-all">
                Tạo Nhóm
            </button>
            <p id="create-group-msg" class="text-xs mt-2 hidden text-center"></p>
        </div>
    </div>
</div>
