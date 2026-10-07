<header class="h-14 border-b border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur flex items-center justify-between px-4 z-40 shrink-0">
    <div class="flex items-center gap-3">
        <a href="{{ route('app.chat-board') }}" class="flex items-center gap-2 font-extrabold text-lg text-sky-500 hover:text-sky-600 transition-colors">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.002-.87 9.31 9.31 0 001.218-4.383C4.208 14.43 3 12.808 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            <span>MessLearn</span>
        </a>
        <span class="px-2 py-0.5 text-[10px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 rounded-md">App Workspace</span>
    </div>

    <div class="flex items-center gap-3">
        <button type="button" onclick="toggleTheme()" class="p-1.5 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" title="Đổi chế độ sáng/tối">
            <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <a href="{{ route('home') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-sky-500 dark:hover:text-sky-400 font-medium transition-colors">Về trang chủ</a>

        <div class="h-4 w-px bg-slate-200 dark:bg-slate-800"></div>

        <div class="flex items-center gap-2 cursor-pointer" onclick="if(typeof openModal === 'function') openModal('modal-user-settings')">
            <div id="header-user-avatar" class="w-7 h-7 rounded-xl bg-sky-500 text-white flex items-center justify-center font-bold text-xs overflow-hidden">
                @if(Auth::user()->avatar_url)
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                @else
                    <span>{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
            <span id="header-user-name" class="text-xs font-semibold text-slate-700 dark:text-slate-200 hidden sm:inline">{{ Auth::user()->name ?? 'Người dùng' }}</span>
        </div>
    </div>
</header>

<script>
    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        document.body.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark');
    }
</script>