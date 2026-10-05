<!-- CỘT 1: SIDEBAR ĐIỀU HƯỚNG HẸP -->
<aside class="w-16 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col items-center py-4 justify-between shrink-0">
    <div class="flex flex-col items-center gap-4 w-full">
        <div class="relative group cursor-pointer" title="{{ Auth::user()->name }}">
            <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-sky-500/30">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
        </div>
        <hr class="w-8 border-slate-200 dark:border-slate-800">
        <nav class="flex flex-col gap-2 w-full px-2">
            <a href="{{ route('app.chat-board') }}" class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center transition-all" title="Trò chuyện">
                <i data-lucide="message-square" class="w-5 h-5"></i>
            </a>
            <a href="#" class="p-2.5 rounded-xl text-slate-400 hover:text-sky-500 hover:bg-sky-50 dark:hover:bg-sky-900/30 flex items-center justify-center transition-all" title="Thêm bạn bè" onclick="openModal('modal-add-friend')">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
            </a>
        </nav>
    </div>
    <div class="flex flex-col gap-2 w-full px-2">
        <form action="{{ route('auth.logout') }}" method="POST" class="w-full">
            @csrf
            <button type="submit" class="w-full p-2.5 rounded-xl text-rose-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 flex items-center justify-center transition-all" title="Đăng xuất">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</aside>
