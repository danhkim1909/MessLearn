# HANDOVER CONTEXT - DỰ ÁN MESSLEARN

> **Tài liệu bàn giao ngữ cảnh kỹ thuật dành cho AI Agent tiếp nối.**

---

## 1. MỤC TIÊU HIỆN TẠI
*   **Dự án:** **MessLearn** - Nền tảng Trò chuyện kết hợp Làm bài tập / Quiz tương tác thời gian thực cho học sinh/sinh viên.
*   **Tech Stack:** PHP 8.4, Laravel 11, MySQL, Blade Template, CSS thuần theo Design System (Sky Blue `#0EA5E9`), tối ưu 0đ chạy Local/Ngrok.
*   **Nhiệm vụ đang thực hiện:** Hoàn thiện luồng Xác thực (Đăng ký / Đăng nhập) và chuẩn bị kiểm tra chạy thử trên giao diện.

---

## 2. TIẾN ĐỘ ĐÃ ĐẠT ĐƯỢC & CÁC FILE ĐÃ TÁC ĐỘNG

### A. Quy chuẩn & Hướng dẫn (Agent Context)
*   `d:/KimDanh/.agent/skills/ui-guidelines/SKILL.md`: Quy tắc UI Sky Blue, bo tròn (`rounded-2xl`, `rounded-md`), tối giản, hỗ trợ Dark/Light mode.
*   `MessLearn/.agent/skills/messlearn-specs/SKILL.md`: Đặc tả tính năng, Data Dictionary chi tiết từng bảng/cột.
*   `MessLearn/.agent/skills/clean-code-standards/SKILL.md`: Quy chuẩn Clean Code (cấm emoji trong code, cấm over-comment, self-documenting).
*   `MessLearn/AGENTS.md`: Tệp chỉ dẫn ngữ cảnh tự động cho Agent.

### B. Database & Migrations (`MessLearn/database/migrations/`)
*   `0001_01_01_000000_create_users_table.php`: Chờ thêm `avatar`, `is_active` (bool, default true), `is_locked` (bool, default false).
*   `2026_10_02_115222_create_friendships_table.php`: Kết bạn (pending, accepted, blocked).
*   `2026_10_02_115223_create_conversations_and_participants_tables.php`: Chat 1-1 / Group & thành viên.
*   `2026_10_02_115224_create_quizzes_tables.php`: `quizzes` (đã bỏ `created_by`), `quiz_questions`, `quiz_options`.
*   `2026_10_02_115225_create_messages_tables.php`: `messages` (interactive messages, `quiz_id` nullable, metadata json), `message_reactions`.
*   `2026_10_02_115226_create_quiz_submissions_tables.php`: `quiz_submissions`, `quiz_answers`.

### C. Eloquent Models (`MessLearn/app/Models/`)
*   Đã tạo đủ 11 models chuẩn Eloquent, `$fillable`, `$casts`, relationships:
    `User.php`, `Friendship.php`, `Conversation.php`, `ConversationParticipant.php`, `Quiz.php`, `QuizQuestion.php`, `QuizOption.php`, `Message.php`, `MessageReaction.php`, `QuizSubmission.php`, `QuizAnswer.php`.

### D. Frontend & Routing
*   `MessLearn/public/css/style.css`: Đã tạo file CSS Design System hoàn chỉnh (Light/Dark mode, Auth Card, Buttons).
*   `MessLearn/routes/web.php`: Route `Route::get('/', [AuthController::class, 'index'])->name('auth');`.
*   `MessLearn/resources/views/user/layouts/app.blade.php`: Layout gốc.
*   `MessLearn/resources/views/user/pages/auth/index.blade.php`: Giao diện Auth chung (Tab chuyển Login/Register).

---

## 3. TRẠNG THÁI HIỆN TẠI & LỖI TỒN ĐỌNG (CẦN SỬA)

Code hiện tại **chưa chạy thử trên trình duyệt** do đang có 3 điểm lệch đường dẫn cần chỉnh:
1.  **`app/Http/Controllers/User/AuthController.php` (dòng 15):** 
    *   Hiện tại: `return view('user.auth.index');`
    *   Cần sửa thành: `return view('user.pages.auth.index');`
2.  **`resources/views/user/pages/auth/index.blade.php` (dòng 1):** 
    *   Hiện tại: `@extends('user.layout.pattern')`
    *   Cần sửa thành: `@extends('user.layouts.app')`
3.  **`resources/views/user/layouts/app.blade.php` (dòng 8):** 
    *   Hiện tại: `<link rel='stylesheet' href='main.css'>`
    *   Cần sửa thành: `<link rel="stylesheet" href="{{ asset('css/style.css') }}">`
4.  **Database:** Cần đảm bảo MySQL đang chạy và chạy lệnh `php artisan migrate:fresh`.

---

## 4. BƯỚC TIẾP THEO (NEXT STEPS - LÀM NGAY LẬP TỨC)

1.  **Fix 3 lỗi đường dẫn** ở mục 3 trên để trang `http://127.0.0.1:8000` hiển thị mượt mà giao diện Đăng nhập / Đăng ký.
2.  **Viết phương thức xử lý Đăng ký / Đăng nhập** trong `AuthController.php`:
    *   `register(Request $request)`: Validate `name`, `email`, `password`, tạo User bằng `User::create(...)`, đăng nhập bằng `Auth::login($user)`.
    *   `login(Request $request)`: Validate, kiểm tra `Auth::attempt(...)`, kiểm tra thêm điều kiện `!$user->is_locked`, tái tạo session.
    *   `logout(Request $request)`: `Auth::logout()`.
3.  **Thêm Route POST** tương ứng trong `routes/web.php`:
    *   `Route::post('/register', [AuthController::class, 'register'])->name('register');`
    *   `Route::post('/login', [AuthController::class, 'login'])->name('login');`
    *   `Route::post('/logout', [AuthController::class, 'logout'])->name('logout');`
4.  Gắn action form và token `@csrf` vào file `index.blade.php`.
5.  Sau khi Auth hoạt động, chuyển sang dựng giao diện Dashboard / Màn hình Chat chính (Giai đoạn 1 MVP).

---
*Quy tắc bắt buộc đối với Agent:* Luôn dùng tiếng Việt, giải thích trực quan cho sinh viên, không tự ý sửa code mà hướng dẫn người dùng tự sửa, tuân thủ `clean-code-standards` (không emoji, không over-comment).
