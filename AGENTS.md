# HƯỚNG DẪN DỰ ÁN MESSLEARN (DÀNH CHO TẤT CẢ CÁC PHIÊN BẢN AI AGENT)

Dự án này là **MessLearn** - Nền tảng Chat kết hợp Học tập & Làm bài tập tương tác thời gian thực cho sinh viên/học sinh, phát triển bằng Laravel 11 + PHP 8.4 + MySQL.

## Các tài liệu quy chuẩn quan trọng bắt buộc đọc:
1. **Quy chuẩn UI/UX:** Xem tại `d:/KimDanh/.agent/skills/ui-guidelines/SKILL.md` (Màu Sky Blue `#0EA5E9`, bo góc mềm mại, tối giản, Light/Dark mode).
2. **Đặc tả nghiệp vụ & Tính năng:** Xem tại `d:/KimDanh/messlearn/.agent/skills/messlearn-specs/SKILL.md`.
3. **Tiêu chuẩn viết code sạch (Bắt buộc tuân thủ):** Xem tại `d:/KimDanh/messlearn/.agent/skills/clean-code-standards/SKILL.md`.

## Nguyên tắc cốt lõi khi sinh code và tương tác:
- Luôn trả lời bằng **tiếng Việt**.
- Người dùng là sinh viên, giải thích cặn kẽ logic, không tự ý sửa code âm thầm mà hướng dẫn để người dùng tự sửa.
- **Phong cách sinh code:** 
  + Hạn chế tối đa comment thừa (chỉ dùng comment để đánh dấu các khối lớn `// --- Section ---`).
  + TUYỆT ĐỐI KHÔNG chèn emoji (🚀, ✨, 💡...) vào comment hay trong file mã nguồn.
  + Viết code ngắn gọn, tự giải thích (self-documenting), không bloat code.
