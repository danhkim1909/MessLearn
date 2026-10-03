@extends('guest.layouts.app')
@section('title', 'MessLearn - Nền tảng Trò chuyện & Học tập nhóm tương tác')

@section('content')
<div class="flex-1 flex flex-col justify-center max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
        <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                <span>Nền tảng học tập nhóm thế hệ mới</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15]">
                Học tập cùng nhau, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 to-blue-600">Trò chuyện thời gian thực</span>
            </h1>

            <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                MessLearn kết hợp không gian chat nhóm trực quan với bộ công cụ Quiz bài tập tự động chấm điểm và mini-game giải trí, giúp sinh viên gắn kết và học tập hiệu quả.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                <a href="{{ route('auth.auth') }}" class="w-full sm:w-auto px-8 py-3.5 bg-sky-500 hover:bg-sky-600 active:scale-95 text-white font-bold text-base rounded-2xl shadow-lg shadow-sky-500/30 transition-all text-center">
                    Bắt đầu ngay miễn phí
                </a>
                <a href="#features" class="w-full sm:w-auto px-8 py-3.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-base rounded-2xl transition-colors text-center">
                    Khám phá tính năng
                </a>
            </div>

            <div class="pt-6 flex items-center justify-center lg:justify-start gap-6 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>100% Miễn phí</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>WebSocket Realtime</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Giao diện tối giản</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5 relative">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center font-bold">ML</div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Nhóm Học Thuật PHP 8.4</h3>
                            <p class="text-xs text-slate-400">4 thành viên đang online</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex gap-2">
                        <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 shrink-0 flex items-center justify-center font-semibold text-slate-600 dark:text-slate-300">A</div>
                        <div class="bg-slate-100 dark:bg-slate-700/60 p-3 rounded-2xl max-w-[80%]">
                            <span class="font-semibold text-slate-900 dark:text-slate-200 block mb-0.5">Minh Anh</span>
                            Mọi người ơi, làm thử bài Quiz ôn tập kiến thức Laravel 11 này nhé!
                        </div>
                    </div>

                    <div class="bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/80 p-3.5 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sky-600 dark:text-sky-400">QUIZ: Kiểm tra Eloquent ORM</span>
                            <span class="px-2 py-0.5 bg-sky-500 text-white text-[10px] font-bold rounded-full">5 câu</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300">Thời gian làm bài: 5 phút</p>
                        <a href="{{ route('auth.auth') }}" class="block w-full py-2 bg-sky-500 hover:bg-sky-600 text-white text-center font-semibold rounded-xl transition-all">Tham gia làm bài</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="features" class="pt-20 lg:pt-32">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tính năng nổi bật của MessLearn</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">Được thiết kế tối giản, tập trung tối đa vào việc trao đổi và ôn luyện nhóm</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white mb-2">Trò chuyện thời gian thực</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Nhắn tin 1-1 và chat nhóm tốc độ cao nhờ tích hợp WebSocket Laravel Reverb chính chủ.</p>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white mb-2">Quiz Engine trong Chat</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Tạo đề thi trắc nghiệm & tự luận ngắn ngay trong khung chat, máy tự động chấm điểm tức thì.</p>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white mb-2">Mini-Game tương tác</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Đổ xúc xắc, oẳn tù tì giảm căng thẳng trực tiếp trong khung trò chuyện cùng bạn học.</p>
            </div>
        </div>
    </div>
</div>
@endsection