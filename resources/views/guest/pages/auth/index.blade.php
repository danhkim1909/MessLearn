@extends('guest.layouts.app')
@section('title', 'MessLearn - Đăng nhập & Đăng ký')

@section('content')
<div class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-xl p-8 transition-colors duration-200">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-sky-500 mb-1">MessLearn</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Nền tảng trò chuyện & học tập nhóm thông minh</p>
        </div>

        @if(session('success'))
            <div class="mb-5 p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-emerald-600 dark:text-emerald-400 text-sm flex items-center gap-2.5">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-rose-600 dark:text-rose-400 text-sm flex items-center gap-2.5">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex bg-slate-100 dark:bg-slate-900/60 p-1.5 rounded-2xl mb-6 border border-slate-200 dark:border-slate-700/60">
            <button type="button" class="flex-1 py-2 text-sm font-semibold rounded-xl transition-all duration-200 text-sky-500 bg-white dark:bg-slate-800 shadow-sm" id="tab-login" onclick="switchTab('login')">Đăng nhập</button>
            <button type="button" class="flex-1 py-2 text-sm font-semibold rounded-xl transition-all duration-200 text-slate-500 dark:text-slate-400" id="tab-register" onclick="switchTab('register')">Đăng ký</button>
        </div>

        <form id="form-login" action="{{ route('auth.login') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border @error('email', 'login') border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all dark:text-white" placeholder="email@domain.com" required value="{{ old('email') }}">
                @error('email', 'login')
                    <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mật khẩu</label>
                <input type="password" name="password" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border @error('password', 'login') border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all dark:text-white" placeholder="••••••••" required>
                @error('password', 'login')
                    <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-sky-500 hover:bg-sky-600 active:scale-[0.98] text-white font-bold text-sm rounded-xl shadow-lg shadow-sky-500/25 transition-all">Đăng nhập ngay</button>
        </form>

        <form id="form-register" action="{{ route('auth.register') }}" method="POST" class="space-y-4 hidden" novalidate>
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Họ và tên của bạn</label>
                <input type="text" name="name" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border @error('name', 'register') border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all dark:text-white" placeholder="Ví dụ: Nguyễn Văn A" value="{{ old('name') }}" required>
                @error('name', 'register')
                    <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border @error('email', 'register') border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all dark:text-white" placeholder="email@domain.com" value="{{ old('email') }}" required>
                @error('email', 'register')
                    <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mật khẩu</label>
                <input type="password" name="password" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border @error('password', 'register') border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all dark:text-white" placeholder="Tối thiểu 8 ký tự" required>
                @error('password', 'register')
                    <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Xác nhận Mật khẩu</label>
                <input type="password" name="re_password" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border @error('re_password', 'register') border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all dark:text-white" placeholder="Tối thiểu 8 ký tự" required>
                @error('re_password', 'register')
                    <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-sky-500 hover:bg-sky-600 active:scale-[0.98] text-white font-bold text-sm rounded-xl shadow-lg shadow-sky-500/25 transition-all">Tạo tài khoản mới</button>
        </form>
    </div>
</div>

<script>
    function switchTab(type) {
        const loginForm = document.getElementById('form-login');
        const registerForm = document.getElementById('form-register');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');

        const activeClasses = ['text-sky-500', 'bg-white', 'dark:bg-slate-800', 'shadow-sm'];
        const inactiveClasses = ['text-slate-500', 'dark:text-slate-400'];

        if (type === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            tabLogin.classList.add(...activeClasses);
            tabLogin.classList.remove(...inactiveClasses);
            tabRegister.classList.remove(...activeClasses);
            tabRegister.classList.add(...inactiveClasses);
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            tabRegister.classList.add(...activeClasses);
            tabRegister.classList.remove(...inactiveClasses);
            tabLogin.classList.remove(...activeClasses);
            tabLogin.classList.add(...inactiveClasses);
        }
    }

    @if($errors->register->any())
        switchTab('register');
    @endif
</script>
@endsection