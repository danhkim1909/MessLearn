<!-- MODAL: MINI-GAMES (XÚC XẮC & OẲN TÙ TÌ) -->
<div id="modal-mini-games" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 w-full max-w-lg rounded-3xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 p-5 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center">
                    <i data-lucide="gamepad-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Mini-game</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Giải trí & Bốc thăm công bằng</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-mini-games')" class="p-2 text-slate-400 hover:text-rose-500 transition-colors rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-100 dark:border-slate-700/60 px-5 pt-3 gap-2 bg-slate-50/50 dark:bg-slate-900/20">
            <button type="button" id="tab-btn-dice" onclick="switchMiniGameTab('dice')" class="pb-2.5 px-3 border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center gap-2 transition-all">
                <i data-lucide="box" class="w-4 h-4"></i>
                Tung Xúc Xắc
            </button>
            <button type="button" id="tab-btn-rps" onclick="switchMiniGameTab('rps')" class="pb-2.5 px-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-xs flex items-center gap-2 transition-all">
                <i data-lucide="swords" class="w-4 h-4"></i>
                Oẳn Tù Tì (1-1)
            </button>
        </div>

        <div class="p-5 flex-1 overflow-y-auto">
            <!-- TAB 1: TUNG XÚC XẮC -->
            <div id="tab-content-dice" class="space-y-4">
                <div class="p-4 bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 rounded-2xl flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center font-black text-xl shadow-md shadow-indigo-500/20 shrink-0">
                        <i data-lucide="box" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Tung xúc xắc 6 mặt</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tạo kết quả ngẫu nhiên từ 1 đến 6 cho cả nhóm cùng theo dõi.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mục đích tung (Không bắt buộc)</label>
                    <input type="text" id="dice-note-input" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors dark:text-white" placeholder="vd: Ai rửa bát?, Chọn người thuyết trình tuần này...">
                </div>

                <div class="pt-2">
                    <button type="button" id="btn-submit-dice" onclick="submitRollDice()" class="w-full py-3 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="box" class="w-4 h-4"></i>
                        Tung Xúc Xắc Ngay
                    </button>
                </div>
            </div>

            <!-- TAB 2: OẲN TÙ TÌ -->
            <div id="tab-content-rps" class="hidden space-y-4">
                <div class="p-4 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40 rounded-2xl flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-xl shadow-md shadow-amber-500/20 shrink-0">
                        <i data-lucide="swords" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Thách đấu Oẳn Tù Tì</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Chọn nước đi bí mật. Người đầu tiên nhận lời đấu sẽ đối chiếu kết quả ngay lập tức!</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Chọn nước đi bí mật của bạn</label>
                    <input type="hidden" id="rps-selected-choice" value="rock">
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" onclick="selectRpsChoice('rock')" id="rps-choice-rock" class="rps-choice-btn p-3.5 rounded-2xl border-2 border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex flex-col items-center justify-center gap-2 transition-all">
                            <i data-lucide="shield" class="w-7 h-7"></i>
                            <span class="font-bold text-xs">Búa</span>
                        </button>
                        <button type="button" onclick="selectRpsChoice('paper')" id="rps-choice-paper" class="rps-choice-btn p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex flex-col items-center justify-center gap-2 transition-all">
                            <i data-lucide="hand" class="w-7 h-7"></i>
                            <span class="font-bold text-xs">Bao</span>
                        </button>
                        <button type="button" onclick="selectRpsChoice('scissors')" id="rps-choice-scissors" class="rps-choice-btn p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex flex-col items-center justify-center gap-2 transition-all">
                            <i data-lucide="scissors" class="w-7 h-7"></i>
                            <span class="font-bold text-xs">Kéo</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Lời thách đấu (Không bắt buộc)</label>
                    <input type="text" id="rps-note-input" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors dark:text-white" placeholder="vd: Ai dám solo nào?, Kèo một cốc trà sữa nhé...">
                </div>

                <div class="pt-2">
                    <button type="button" id="btn-submit-rps" onclick="submitCreateRps()" class="w-full py-3 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Gửi Lời Thách Đấu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
