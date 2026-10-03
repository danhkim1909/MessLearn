@extends('user.layouts.app')
@section('title', 'MessLearn - Cửa sổ trò chuyện & Học tập')

@section('content')
<div class="flex-1 flex overflow-hidden h-[calc(100vh-4rem)]">
    <!-- CỘT 1: SIDEBAR ĐIỀU HƯỚNG HẸP -->
    <aside class="w-16 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col items-center py-4 justify-between shrink-0">
        <div class="flex flex-col items-center gap-4 w-full">
            <div class="relative group cursor-pointer">
                <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-sky-500/30">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
            </div>

            <hr class="w-8 border-slate-200 dark:border-slate-800">

            <nav class="flex flex-col gap-2 w-full px-2">
                <a href="{{ route('app.chat-board') }}" class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center transition-all" title="Trò chuyện">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </a>

                <a href="#" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-all" title="Bạn bè">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </a>

                <a href="#" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-all" title="Kho Quiz Bài tập">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </nav>
        </div>

        <div class="flex flex-col items-center gap-3 w-full px-2">
            <form action="{{ route('auth.logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full p-2.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center transition-all" title="Đăng xuất">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- CỘT 2: DANH SÁCH CUỘC TRÒ CHUYỆN -->
    <section class="w-72 bg-slate-50 dark:bg-slate-900/50 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h2 class="font-extrabold text-lg text-slate-900 dark:text-white">Tin nhắn</h2>
            <button type="button" class="p-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow-sm transition-all" title="Tạo nhóm mới">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <div class="p-3">
            <div class="relative">
                <input type="text" placeholder="Tìm kiếm hội thoại..." class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-sky-100 dark:border-slate-700/80 shadow-sm cursor-pointer transition-all">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center font-bold text-xs">PHP</div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-800 rounded-full"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">Học Thuật PHP 8.4</h4>
                            <span class="text-[10px] text-slate-400">10:42</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Minh Anh: Đã gửi bài Quiz ôn tập</p>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-2xl hover:bg-white dark:hover:bg-slate-800/60 cursor-pointer transition-all">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500 text-white flex items-center justify-center font-bold text-xs">MA</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">Minh Anh</h4>
                            <span class="text-[10px] text-slate-400">Hôm qua</span>
                        </div>
                        <p class="text-xs text-slate-400 truncate">Cảm ơn bạn nhé!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CỘT 3: CỬA SỔ CHAT VÀ TƯƠNG TÁC CHÍNH -->
    <main class="flex-1 bg-white dark:bg-slate-900 flex flex-col min-w-0">
        <!-- HEADER CHAT -->
        <div class="h-16 px-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-sky-500 text-white flex items-center justify-center font-bold text-xs">PHP</div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Nhóm Học Thuật PHP 8.4</h3>
                    <p class="text-[11px] text-slate-400">4 thành viên • 2 online</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Ghim tin nhắn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- KHUNG CHAT STREAM -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-2xl bg-indigo-500 text-white flex items-center justify-center font-bold text-xs shrink-0">MA</div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">Minh Anh</span>
                        <span class="text-[10px] text-slate-400">10:40</span>
                    </div>
                    <div class="bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-4 py-2.5 rounded-2xl rounded-tl-sm text-xs max-w-md">
                        Mọi người ơi, mình mới tạo một đề trắc nghiệm kiểm tra kiến thức Laravel 11. Cùng làm nhé!
                    </div>
                </div>
            </div>

            <!-- CARD QUIZ TRONG CHAT -->
            <div class="ml-11 max-w-md bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/80 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-xs text-sky-600 dark:text-sky-400">QUIZ BÀI TẬP</span>
                    <span class="px-2 py-0.5 bg-sky-500 text-white text-[10px] font-bold rounded-full">5 câu trắc nghiệm</span>
                </div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white">Ôn tập Eloquent ORM & Migrations</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Thời gian làm bài: 5 phút • Tự động chấm điểm</p>
                <button type="button" class="w-full py-2 bg-sky-500 hover:bg-sky-600 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-sky-500/20 transition-all">
                    Bắt đầu làm bài Quiz
                </button>
            </div>
        </div>

        <!-- FOOTER NHẬP TIN NHẮN -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 shrink-0">
            <form action="#" method="POST" class="flex items-center gap-2" onsubmit="return false;">
                <button type="button" class="p-2.5 text-slate-400 hover:text-sky-500 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-xl transition-all" title="Soạn Quiz mới">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>

                <input type="text" placeholder="Nhập tin nhắn..." class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">

                <button type="submit" class="p-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow-md shadow-sky-500/20 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
        </div>
    </main>
</div>
@endsection
