---
name: clean-code-standards
description: >-
  Tiêu chuẩn viết code thực tế, loại bỏ phong cách AI rườm rà (AI code smells).
  Hạn chế tối đa comment thừa, cấm chèn emoji/icon bừa bãi, ưu tiên Tailwind CSS & self-documenting code.
---

# TIÊU CHUẨN VIẾT CODE SẠCH (LOẠI BỎ PHONG CÁCH AI)

Khi sinh mã nguồn (PHP, JavaScript, Blade, CSS, SQL), AI Agent BẮT BUỘC phải tuân thủ các quy tắc dưới đây để code trông tự nhiên, chuyên nghiệp như lập trình viên con người (Senior Developer) viết, tránh các "mùi code" đặc trưng của AI.

---

## 1. Quy tắc về Comment (Chú thích mã nguồn)

*   **Tuyệt đối KHÔNG comment giải thích dông dài hoặc những điều hiển nhiên:**
    *   ❌ *Xấu (Kiểu AI):*
        ```php
        // Khởi tạo biến user từ database theo id truyền vào
        $user = User::find($id);
        // Trả về view giao diện kèm biến user
        return view('profile', compact('user'));
        ```
    *   ✅ *Tốt (Kiểu con người):*
        ```php
        $user = User::findOrFail($id);
        return view('profile', compact('user'));
        ```
*   **Chỉ comment ở mức ĐÁNH DẤU SECTION NẮNG GỌN (Section Marker):**
    *   Chỉ dùng comment ngắn gọn dạng thẻ/tiêu đề để phân tách các khối logic lớn:
        ```php
        // --- Authentication ---
        
        // --- Helpers ---
        ```
    *   Tuyệt đối không giải thích từng dòng code.

---

## 2. Quy tắc về Emoji & Icon Ký Tự trong Code & Comment

*   **CẤM TUYỆT ĐỐI chèn Emoji hoặc các ký tự icon trang trí (như 🚀, ✨, 🧼, 📚, 🎨, ☀️, 🌙, ⭐, 📌, 🛠, 📍, 💡, 💬, 📝, 🎲) vào:**
    *   Mã nguồn (PHP, JS, Blade, CSS, SQL).
    *   Comment trong code hoặc file kịch bản.
    *   Tên biến, tên hàm, docblock.
*   Code phải giữ vẻ nghiêm túc, tinh gọn và chuẩn mực kỹ thuật chuyên nghiệp.

---

## 3. Quy tắc về CSS & Tailwind CSS

*   **Tối ưu bằng Tailwind CSS:**
    *   Ưu tiên dùng các utility class của Tailwind CSS sẵn có trong Blade View (ví dụ: `flex items-center justify-between p-4 bg-slate-900 text-white rounded-2xl`).
    *   Tránh viết các file CSS custom thủ công dài dòng không cần thiết nếu Tailwind đã hỗ trợ class tương đương.
    *   Giữ file CSS tùy chỉnh gọn nhẹ, chỉ dành cho các biến theme hoặc animation phức tạp.

---

## 4. Code tự giải thích (Self-Documenting Code)

*   Thay vì viết code mơ hồ rồi kèm một dòng comment giải thích bên cạnh, hãy:
    *   Đặt tên hàm rõ động từ: `isAccountLocked()`, `calculateQuizScore()`, `markAsRead()`.
    *   Đặt tên biến thể hiện đúng bản chất dữ liệu: `$unansweredQuestions`, `$activeParticipants`.
    *   Tách hàm nhỏ nếu một phương thức quá dài hoặc làm nhiều hơn 1 nhiệm vụ.

---

## 5. Tinh gọn cấu trúc (Tránh Bloat Code của AI)

*   Tận dụng tối đa các hàm có sẵn của Laravel / PHP.
*   Không tạo các tầng trừu tượng (Interfaces, Repositories, Services) quá sớm khi nghiệp vụ còn đơn giản; giữ code đi thẳng vào trọng tâm.

